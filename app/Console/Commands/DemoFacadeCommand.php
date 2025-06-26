<?php

namespace App\Console\Commands;

use App\Patterns\Facade\TripBookingFacade;
use Illuminate\Console\Command;

class DemoFacadeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:facade
                            {--action=book : Action to perform (book, cancel, track, rate)}
                            {--user-id=1 : User ID}
                            {--trip-id=1 : Trip ID}
                            {--rating=5 : Rating (1-5)}
                            {--comment= : Rating comment}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demo Facade Pattern với hệ thống đặt chuyến đi';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $facade = new TripBookingFacade();
        $action = $this->option('action');

        echo "=== DEMO FACADE PATTERN - HỆ THỐNG ĐẶT CHUYẾN ĐI ===\n\n";

        switch ($action) {
            case 'book':
                $this->demoBooking($facade);
                break;

            case 'cancel':
                $this->demoCancellation($facade);
                break;

            case 'track':
                $this->demoTracking($facade);
                break;

            case 'rate':
                $this->demoRating($facade);
                break;

            default:
                $this->demoAllActions($facade);
                break;
        }

        $this->newLine();
        $this->info('Demo hoàn thành!');
        $this->line('Các lệnh có thể sử dụng:');
        $this->line('php artisan demo:facade --action=book');
        $this->line('php artisan demo:facade --action=cancel --trip-id=1 --user-id=1');
        $this->line('php artisan demo:facade --action=track --trip-id=1');
        $this->line('php artisan demo:facade --action=rate --trip-id=1 --user-id=1 --rating=5 --comment="Tuyệt vời!"');
    }

    /**
     * Demo đặt chuyến đi
     */
    private function demoBooking(TripBookingFacade $facade): void
    {
        echo "🎯 DEMO ĐẶT CHUYẾN ĐI\n";
        echo "=====================\n\n";

        $bookingData = [
            'user_id' => 1,
            'pickup_location' => '123 Nguyễn Huệ, Quận 1, TP.HCM',
            'dropoff_location' => '456 Lê Lợi, Quận 3, TP.HCM',
            'pickup_lat' => 10.762622,
            'pickup_lng' => 106.660172,
            'dropoff_lat' => 10.782622,
            'dropoff_lng' => 106.680172,
            'estimated_fare' => 150000,
            'payment_method' => 'credit_card',
            'vehicle_type' => 'car'
        ];

        echo "📋 Thông tin đặt chuyến:\n";
        echo "   - Điểm đón: {$bookingData['pickup_location']}\n";
        echo "   - Điểm đến: {$bookingData['dropoff_location']}\n";
        echo "   - Cước phí: " . number_format($bookingData['estimated_fare']) . " VND\n";
        echo "   - Phương thức thanh toán: {$bookingData['payment_method']}\n\n";

        $result = $facade->bookTrip($bookingData);

        if ($result['success']) {
            echo "✅ ĐẶT CHUYẾN ĐI THÀNH CÔNG!\n";
            echo "   - Trip ID: {$result['trip_id']}\n";
            echo "   - Tài xế: {$result['driver']['name']}\n";
            echo "   - Thời gian đến: {$result['estimated_arrival']}\n";
        } else {
            echo "❌ ĐẶT CHUYẾN ĐI THẤT BẠI: {$result['message']}\n";
        }
    }

    /**
     * Demo hủy chuyến đi
     */
    private function demoCancellation(TripBookingFacade $facade): void
    {
        echo "🚫 DEMO HỦY CHUYẾN ĐI\n";
        echo "======================\n\n";

        $tripId = $this->option('trip-id');
        $userId = $this->option('user-id');

        echo "📋 Thông tin hủy chuyến:\n";
        echo "   - Trip ID: {$tripId}\n";
        echo "   - User ID: {$userId}\n\n";

        $result = $facade->cancelTrip($tripId, $userId);

        if ($result['success']) {
            echo "✅ HỦY CHUYẾN ĐI THÀNH CÔNG!\n";
            if (isset($result['refund_amount']) && $result['refund_amount'] > 0) {
                echo "   - Số tiền hoàn: " . number_format($result['refund_amount']) . " VND\n";
            }
        } else {
            echo "❌ HỦY CHUYẾN ĐI THẤT BẠI: {$result['message']}\n";
        }
    }

    /**
     * Demo theo dõi chuyến đi
     */
    private function demoTracking(TripBookingFacade $facade): void
    {
        echo "📍 DEMO THEO DÕI CHUYẾN ĐI\n";
        echo "==========================\n\n";

        $tripId = $this->option('trip-id');

        echo "📋 Thông tin theo dõi:\n";
        echo "   - Trip ID: {$tripId}\n\n";

        $result = $facade->trackTrip($tripId);

        if ($result['success']) {
            echo "✅ THÔNG TIN CHUYẾN ĐI:\n";
            echo "   - Trạng thái: {$result['trip']['status']}\n";
            echo "   - Tài xế: {$result['driver']['name']}\n";
            echo "   - Vị trí hiện tại: {$result['current_location']['lat']}, {$result['current_location']['lng']}\n";
            echo "   - Thời gian đến: {$result['estimated_arrival']}\n";
        } else {
            echo "❌ KHÔNG TÌM THẤY CHUYẾN ĐI: {$result['message']}\n";
        }
    }

    /**
     * Demo đánh giá chuyến đi
     */
    private function demoRating(TripBookingFacade $facade): void
    {
        echo "⭐ DEMO ĐÁNH GIÁ CHUYẾN ĐI\n";
        echo "==========================\n\n";

        $tripId = $this->option('trip-id');
        $userId = $this->option('user-id');
        $rating = $this->option('rating');
        $comment = $this->option('comment') ?: 'Chuyến đi tốt';

        echo "📋 Thông tin đánh giá:\n";
        echo "   - Trip ID: {$tripId}\n";
        echo "   - User ID: {$userId}\n";
        echo "   - Đánh giá: {$rating}/5\n";
        echo "   - Nhận xét: {$comment}\n\n";

        $result = $facade->rateTrip($tripId, $userId, $rating, $comment);

        if ($result['success']) {
            echo "✅ ĐÁNH GIÁ THÀNH CÔNG!\n";
            echo "   - Rating ID: {$result['rating_id']}\n";
        } else {
            echo "❌ ĐÁNH GIÁ THẤT BẠI: {$result['message']}\n";
        }
    }

    /**
     * Demo tất cả các actions
     */
    private function demoAllActions(TripBookingFacade $facade): void
    {
        echo "🎯 DEMO TẤT CẢ CÁC CHỨC NĂNG\n";
        echo "=============================\n\n";

        // 1. Đặt chuyến đi
        echo "1️⃣ ĐẶT CHUYẾN ĐI:\n";
        $this->demoBooking($facade);
        echo "\n";

        // 2. Theo dõi chuyến đi
        echo "2️⃣ THEO DÕI CHUYẾN ĐI:\n";
        $this->demoTracking($facade);
        echo "\n";

        // 3. Đánh giá chuyến đi
        echo "3️⃣ ĐÁNH GIÁ CHUYẾN ĐI:\n";
        $this->demoRating($facade);
        echo "\n";

        // 4. Hủy chuyến đi
        echo "4️⃣ HỦY CHUYẾN ĐI:\n";
        $this->demoCancellation($facade);
        echo "\n";

        echo "🎉 HOÀN THÀNH DEMO TẤT CẢ CHỨC NĂNG!\n";
    }
}