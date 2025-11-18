import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// API Configuration
const API_BASE_URL = __DEV__
  ? 'http://localhost:8000/api/v1'
  : 'https://api.ichri.tn/api/v1';

// Create axios instance
const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor
api.interceptors.request.use(
  async (config) => {
    const token = await AsyncStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor
api.interceptors.response.use(
  (response) => {
    return response.data;
  },
  async (error) => {
    const originalRequest = error.config;

    // Handle 401 errors (unauthorized)
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;

      try {
        const refreshToken = await AsyncStorage.getItem('refresh_token');
        const response = await axios.post(
          `${API_BASE_URL}/auth/refresh`,
          { refresh_token: refreshToken }
        );

        const { token } = response.data;
        await AsyncStorage.setItem('auth_token', token);

        originalRequest.headers.Authorization = `Bearer ${token}`;
        return api(originalRequest);
      } catch (refreshError) {
        // Refresh failed, logout user
        await AsyncStorage.multiRemove(['auth_token', 'refresh_token', 'user']);
        // Navigate to login
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error.response?.data || error);
  }
);

// API methods
export const authAPI = {
  register: (data) => api.post('/auth/register', data),
  login: (phone, password) => api.post('/auth/login', { phone, password }),
  sendOTP: (phone) => api.post('/auth/send-otp', { phone }),
  verifyOTP: (phone, otp) => api.post('/auth/verify-otp', { phone, otp }),
  resetPassword: (phone, password, otp) =>
    api.post('/auth/reset-password', { phone, password, otp }),
  logout: () => api.post('/auth/logout'),
  me: () => api.get('/auth/me'),
};

export const catalogAPI = {
  getCategories: () => api.get('/catalog/categories'),
  getBrands: () => api.get('/catalog/brands'),
  getProducts: (params) => api.get('/catalog/products', { params }),
  getProduct: (id) => api.get(`/catalog/products/${id}`),
  getFeatured: () => api.get('/catalog/products/featured'),
  getOnSale: () => api.get('/catalog/products/on-sale'),
  getBestsellers: () => api.get('/catalog/products/bestsellers'),
  search: (query) => api.get('/catalog/products/search', { params: { q: query } }),
};

export const cartAPI = {
  getCart: () => api.get('/cart'),
  addToCart: (productId, quantity) =>
    api.post('/cart/add', { product_id: productId, quantity }),
  updateCart: (itemId, quantity) =>
    api.put(`/cart/update/${itemId}`, { quantity }),
  removeFromCart: (itemId) => api.delete(`/cart/remove/${itemId}`),
  clearCart: () => api.delete('/cart/clear'),
};

export const orderAPI = {
  getOrders: (params) => api.get('/orders', { params }),
  getOrder: (id) => api.get(`/orders/${id}`),
  createOrder: (data) => api.post('/orders', data),
  cancelOrder: (id, reason) => api.post(`/orders/${id}/cancel`, { reason }),
  reorder: (id) => api.post(`/orders/${id}/reorder`),
};

export const userAPI = {
  getProfile: () => api.get('/user/profile'),
  updateProfile: (data) => api.put('/user/profile', data),
  uploadDocument: (data) => api.post('/user/documents', data),
  getCreditInfo: () => api.get('/user/credit'),
  getStatistics: () => api.get('/user/statistics'),
  getFavorites: () => api.get('/favorites'),
  toggleFavorite: (productId) => api.post(`/favorites/toggle/${productId}`),
};

export default api;
