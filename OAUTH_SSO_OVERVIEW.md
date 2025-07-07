# 🔐 OAuth + SSO Integration Overview

## 🎯 Khái Niệm

### **OAuth**
- **Định nghĩa**: OAuth là một giao thức uỷ quyền cho phép người dùng truy cập vào tài nguyên trên 1
ứng dụng bằng cách xác thực thông qua một bên thứ 3 chứ ko cần thông tin đăng nhập.
- **Lợi ích**: Bảo mật, kiểm soát, phạm vi truy cập, thời gian hạn chế

### **OAuth Flow (Google)**
```
1. User clicks "Login with Google"
                  ↓
2. Laravel App redirects to Google OAuth URL
                  ↓
3. User authenticates and grants permissions (Google)
                  ↓
4. Google redirects back to Laravel Callback URL with authorization code
                  ↓
5. Laravel App sends code + client_id + client_secret to Google
                  ↓
6. Google returns access_token
                  ↓
7. Laravel App uses access_token to get user info from Google
                  ↓
8. Google returns user information (name, email, avatar)
                  ↓
9. Laravel App finds or creates User in database, logs in user
                  ↓
10. Laravel App redirects user to dashboard (logged in)
```

---

## 🔑 SSO (Single Sign-On)

### **SSO (Single Sign-On)**
- **Định nghĩa**: Phương pháp xác thực cho phép đăng nhập một lần, truy cập nhiều ứng dụng khác nhau
của cùng 1 hệ thống hoặc các hệ thống khác nhau trong 1 hệ sinh thái mà không cần
đăng nhập lại, kiểm soát tập trung nhằm giảm rủi ro và thu hồi quyền nhanh chóng
- **Lợi ích**: Trải nghiệm người dùng tốt, giảm rủi ro bảo mật, quản lý tập trung

### **SSO Flow**

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   User          │    │   SSO Service   │    │   Applications  │
│                 │    │                 │    │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │ 1. Login Request      │                       │
         │──────────────────────▶│                       │
         │                       │                       │
         │ 2. SSO Token          │                       │
         │◀──────────────────────│                       │
         │                       │                       │
         │ 3. Access App A       │                       │
         │──────────────────────────────────────────────▶│
         │                       │                       │
         │ 4. Validate Token     │                       │
         │──────────────────────▶│                       │
         │                       │                       │
         │ 5. Valid Response     │                       │
         │◀──────────────────────│                       │
         │                       │                       │
         │ 6. Access App B       │                       │
         │──────────────────────────────────────────────▶│
         │                       │                       │
         │ 7. Same Token         │                       │
         │──────────────────────▶│                       │
         │                       │                       │
         │ 8. Valid Response     │                       │
         │◀──────────────────────│                       │
```

### **Session Lifecycle**

#### **1. Session Creation**
```php
// Tạo SSO session
$sessionData = [
    'user_id' => $user->id,
    'provider' => 'google',
    'created_at' => now()->timestamp,
    'expires_at' => now()->addHour()->timestamp,
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent()
];

Cache::put("sso_session_{$ssoToken}", $sessionData, 3600);
```

#### **2. Session Validation**
```php
// Validate session
public function validateSession(string $ssoToken): bool
{
    $sessionData = Cache::get("sso_session_{$ssoToken}");

    if (!$sessionData) {
        return false; // Session not found
    }

    if ($sessionData['expires_at'] < now()->timestamp) {
        Cache::forget("sso_session_{$ssoToken}"); // Cleanup
        return false; // Session expired
    }

    return true; // Session valid
}
```

#### **3. Session Revocation**
```php
// Revoke session
public function revokeSession(string $ssoToken): bool
{
    $sessionData = Cache::get("sso_session_{$ssoToken}");
    if ($sessionData) {
        Cache::forget("sso_session_{$ssoToken}");
        Log::info("Session revoked for user: {$sessionData['user_id']}");
        return true;
    }
    return false;
}
```
---

## 📁 Cấu Trúc File

```
app/
├── Enums/
│   ├── AuthProviderEnum.php      # Quản lý providers (phone, google)
│   └── GoogleOAuthEnum.php       # Cấu hình Google OAuth
├── Services/
│   └── SSOService.php            # Xử lý logic OAuth + SSO
├── Http/
│   └── Controllers/
│       └── AuthController.php    # Controller
└── Models/
    └── User.php                  # User model với OAuth fields
```

---

## Tích Hợp OAuth + SSO

### **Bước 1: FE Call API**
- [💻 Code Frontend](#-code-examples)

### **Bước 2: AuthController Processing**
```php
// AuthController::authenticateWithGoogle()
public function authenticateWithGoogle(Request $request)
{
    // Validate input
    $request->validate(['oauth_token' => 'required|string']);

    // Authenticate với SSO service
    $result = $this->ssoService->authenticate(
        AuthProviderEnum::GOOGLE,
        ['oauth_token' => $request->input('oauth_token')]
    );

    return response()->json(['data' => $result]);
}
```

### **Bước 3: SSOService OAuth Flow**
```php
// SSOService::authenticateOAuth()
private function authenticateOAuth(string $provider, array $credentials): ?array
{
    // Validate OAuth token
    $oauthToken = $credentials['oauth_token'];

    // Get user info from Google API
    $googleUserData = $this->getGoogleUserInfo($oauthToken);

    // Create or get user
    $user = $this->createOrGetGoogleUser($googleUserData);

    // Create SSO session
    return $this->createSSOSession($user, $provider);
}
```

### **Bước 4: Google API Integration**
```php
// SSOService::getGoogleUserInfo()
private function getGoogleUserInfo(string $oauthToken): ?array
{
    $response = Http::withHeaders([
        'Authorization' => "Bearer {$oauthToken}"
    ])->get(GoogleOAuthEnum::USER_INFO_URL);

    return $response->successful() ? $response->json() : null;
}
```

### **Bước 5: User Management**
```php
// SSOService::createOrGetGoogleUser()
private function createOrGetGoogleUser(array $googleData): ?User
{
    // Check existing user by Google ID
    $user = User::where('google_id', $googleData['id'])->first();

    if (!$user) {
        // Create new user or link existing
        $user = User::create([
            'name' => $googleData['name'],
            'email' => $googleData['email'],
            'google_id' => $googleData['id'],
            'provider' => 'google'
        ]);
    }

    return $user;
}
```

### **Bước 6: SSO Session Creation**
```php
// SSOService::createSSOSession()
private function createSSOSession(User $user, string $provider): array
{
    // Generate SSO token
    $ssoToken = Str::random(64);

    // Store session in cache
    Cache::put("sso_session_{$ssoToken}", [
        'user_id' => $user->id,
        'provider' => $provider,
        'expires_at' => now()->addHour()->timestamp
    ], 3600);

    return [
        'sso_token' => $ssoToken,
        'user' => $user->toArray()
    ];
}
```


## 💻 Code Frontend ( Vue 3 + Piana + Typescript)

### **AuthCompoment.vue**
```vue
<template>
  <div>
    <button @click="loginWithGoogle" :disabled="loading">
      {{ loading ? 'Login...' : 'Login with Google' }}
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { apiService } from '@/services/api'

const authStore = useAuthStore()
const loading = ref(false)

const loginWithGoogle = async (): Promise<void> => {
  try {
    loading.value = true

    // 1. Lấy Google OAuth token từ Google SDK
    const googleToken = await getGoogleToken()

    // 2. Gọi API OAuth
    const response = await apiService.post('/api/v1/sso/google', {
      oauth_token: googleToken
    })

    // 3. Lưu SSO token và user data
    const { sso_token, user } = response.data
    localStorage.setItem('sso_token', sso_token)
    localStorage.setItem('user', JSON.stringify(user))

    // 4. Update auth store
    authStore.setAuth({ ssoToken: sso_token, user })

  } catch (error) {
    console.error('OAuth error:', error)
  } finally {
    loading.value = false
  }
}
</script>
```

### **Auth Store**
```typescript
// stores/auth.ts
import { defineStore } from 'pinia'
import { ref } from 'vue'

interface User {
  id: number
  name: string
  email: string
  provider: string
  avatar?: string
}

interface AuthData {
  ssoToken: string
  user: User
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const ssoToken = ref<string | null>(null)

  const setAuth = ({ ssoToken: token, user: userData }: AuthData): void => {
    ssoToken.value = token
    user.value = userData
  }

  const clearAuth = (): void => {
    ssoToken.value = null
    user.value = null
    localStorage.removeItem('sso_token')
    localStorage.removeItem('user')
  }

  return { user, ssoToken, setAuth, clearAuth }
})
```

### **API Service**
```typescript
// services/api.ts
interface ApiResponse<T> {
  data: T
  message: string
}

interface OAuthResponse {
  sso_token: string
  user: {
    id: number
    name: string
    email: string
    provider: string
    avatar?: string
  }
}

class ApiService {
  private baseURL: string

  constructor() {
    this.baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8000'
  }

  private getHeaders(): HeadersInit {
    const headers: HeadersInit = {
      'Content-Type': 'application/json'
    }

    const token = localStorage.getItem('sso_token')
    if (token) {
      headers.Authorization = `Bearer ${token}`
    }

    return headers
  }

  async post<T>(endpoint: string, data: any): Promise<ApiResponse<T>> {
    const response = await fetch(`${this.baseURL}${endpoint}`, {
      method: 'POST',
      headers: this.getHeaders(),
      body: JSON.stringify(data)
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    return response.json()
  }

  async get<T>(endpoint: string): Promise<ApiResponse<T>> {
    const response = await fetch(`${this.baseURL}${endpoint}`, {
      method: 'GET',
      headers: this.getHeaders()
    })

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    return response.json()
  }
}

export const apiService = new ApiService()
```
