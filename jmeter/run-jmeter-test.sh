#!/bin/bash

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration - Updated paths for jmeter folder
PROJECT_NAME="ride-share-be"
JMETER_CONTAINER="${PROJECT_NAME}_jmeter"
TEST_PLAN="ride-share-benchmark.jmx"
RESULTS_FILE="results.jtl"
LOG_FILE="jmeter.log"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

echo -e "${BLUE}🚀 JMeter Benchmark Test for Ride Share API${NC}"
echo -e "${BLUE}============================================${NC}"
echo ""

# Function to print colored output
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Check if Docker is running
check_docker() {
    print_info "Checking Docker status..."
    if ! docker info > /dev/null 2>&1; then
        print_error "Docker is not running. Please start Docker first."
        exit 1
    fi
    print_status "Docker is running"
}

# Check if docker-compose.yml exists
check_compose_file() {
    if [ ! -f "../docker-compose.yml" ]; then
        print_error "docker-compose.yml not found in parent directory"
        exit 1
    fi
    print_status "docker-compose.yml found"
}

# Start services if not running
start_services() {
    print_info "Starting services..."

    # Change to parent directory for docker-compose
    cd ..

    # Check if services are running
    if ! docker-compose ps | grep -q "Up"; then
        print_warning "Services are not running. Starting them..."
        docker-compose up -d
        print_info "Waiting for services to be ready..."
        sleep 10
    else
        print_status "Services are already running"
    fi

    # Return to jmeter directory
    cd jmeter
}

# Clean up old results
cleanup_results() {
    print_info "Cleaning up old results..."
    rm -f "$RESULTS_FILE" "$LOG_FILE"
    print_status "Old results cleaned"
}

# Run JMeter test
run_jmeter_test() {
    print_info "Running JMeter benchmark test..."
    print_info "Test Plan: $TEST_PLAN"
    print_info "Results will be saved to: $RESULTS_FILE"
    print_info "Logs will be saved to: $LOG_FILE"
    echo ""

    # Run JMeter test
    docker-compose exec -T jmeter jmeter -n -t "/jmeter/$TEST_PLAN" -l "/jmeter/$RESULTS_FILE" -j "/jmeter/$LOG_FILE"

    if [ $? -eq 0 ]; then
        print_status "JMeter test completed successfully"
    else
        print_error "JMeter test failed"
        exit 1
    fi
}

# Generate HTML report
generate_report() {
    print_info "Generating HTML report..."

    # Clean up old html-report directory in container
    docker-compose exec -T jmeter rm -rf "/jmeter/html-report" 2>/dev/null || true

    # Generate JMeter HTML report
    print_info "Creating HTML report from JMeter results..."
    docker-compose exec -T jmeter jmeter -g "/jmeter/$RESULTS_FILE" -o "/jmeter/html-report"

    # Đảm bảo container jmeter đang chạy trước khi copy report
    if ! docker-compose ps | grep -E 'jmeter' | grep -q 'Up'; then
        print_info "JMeter container is not running. Restarting..."
        docker-compose up -d jmeter
        sleep 3
    fi

    # Check if report was created successfully
    if [ -f "html-report/index.html" ]; then
        print_status "HTML report created successfully!"
    else
        print_warning "HTML report may not have been created properly"
    fi
}

# Main execution
main() {
    echo -e "${BLUE}Starting JMeter benchmark test...${NC}"
    echo ""

    check_docker
    check_compose_file
    start_services
    cleanup_results
    run_jmeter_test
    generate_report

    echo ""
    echo -e "${GREEN}🎉 Test completed successfully!${NC}"
    echo ""
    echo -e "${BLUE}📁 Generated files:${NC}"
    echo "- Results: $RESULTS_FILE"
    echo "- Logs: $LOG_FILE"
    echo "- HTML Report: html-report/index.html"
    echo ""

    echo -e "${BLUE}📋 To view the HTML report later, open:${NC}"
    echo "file://$(pwd)/html-report/index.html"
    echo ""
    echo -e "${YELLOW}💡 Tip: You can also run this script again with the same results${NC}"

    echo ""
    echo -e "${GREEN}✅ All done! Check the html-report folder for detailed results.${NC}"
}

# Run main function
main "$@"