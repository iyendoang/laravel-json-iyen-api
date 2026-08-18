import axios, {AxiosInstance, AxiosRequestConfig, AxiosResponse, AxiosError} from 'axios';

const baseURL = '/api/v1';

const api: AxiosInstance = axios.create({
    baseURL,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
    timeout: 30000,
});

let isRefreshing = false;
let failedQueue: Array<{
    resolve: (value?: unknown) => void;
    reject: (reason?: any) => void;
}> = [];

const processQueue = (error: AxiosError | null, token: string | null = null) => {
    failedQueue.forEach((prom) => {
        if (error) {
            prom.reject(error);
        } else {
            prom.resolve(token);
        }
    });
    failedQueue = [];
};

// Request interceptor
api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token');

        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }

        // 🔥 PENTING: Jangan set Content-Type untuk FormData
        // Biarkan browser yang set otomatis dengan boundary
        if (config.data instanceof FormData) {
            delete config.headers['Content-Type'];
        }

        // 🔥 Debug log
        if (config.method?.toUpperCase() === 'PUT' || config.method?.toUpperCase() === 'POST') {
            console.log('📤 API Request:', {
                url: config.url,
                method: config.method,
                data: config.data instanceof FormData ? 'FormData' : config.data,
                contentType: config.headers['Content-Type'],
            });
        }

        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response interceptor
api.interceptors.response.use(
    (response: AxiosResponse) => {
        return response;
    },
    async (error: AxiosError) => {
        const originalRequest = error.config as AxiosRequestConfig & { _retry?: boolean };

        // Skip refresh untuk auth endpoints
        const skipUrls = ['/auth/refresh', '/auth/login', '/auth/register', '/auth/logout'];
        if (originalRequest.url && skipUrls.some(url => originalRequest.url?.includes(url))) {
            return Promise.reject(error);
        }

        // Jangan refresh untuk 422 (validation error)
        if (error.response?.status === 422) {
            return Promise.reject(error);
        }

        // Jika 401 dan bukan auth endpoint
        if (error.response?.status === 401 && !originalRequest._retry) {
            const token = localStorage.getItem('token');

            if (!token) {
                return Promise.reject(error);
            }

            if (isRefreshing) {
                return new Promise((resolve, reject) => {
                    failedQueue.push({resolve, reject});
                }).then((newToken) => {
                    originalRequest.headers = originalRequest.headers || {};
                    originalRequest.headers.Authorization = `Bearer ${newToken}`;
                    return api(originalRequest);
                }).catch((err) => {
                    return Promise.reject(err);
                });
            }

            originalRequest._retry = true;
            isRefreshing = true;

            try {
                const response = await axios.post(
                    `${baseURL}/auth/refresh`,
                    {},
                    {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json',
                        },
                    }
                );

                const newToken = response.data.data.access_token;
                localStorage.setItem('token', newToken);

                originalRequest.headers = originalRequest.headers || {};
                originalRequest.headers.Authorization = `Bearer ${newToken}`;

                processQueue(null, newToken);
                return api(originalRequest);
            } catch (refreshError) {
                processQueue(refreshError as AxiosError, null);
                localStorage.removeItem('token');

                if (window.location.pathname !== '/login') {
                    window.location.href = '/login';
                }

                return Promise.reject(refreshError);
            } finally {
                isRefreshing = false;
            }
        }

        return Promise.reject(error);
    }
);

export default api;