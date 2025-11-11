import axios from 'axios';

// Configurar axios para incluir el token CSRF
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Interceptor para agregar la fecha/hora del cliente en cada petición
axios.interceptors.request.use(
    (config) => {
        // Agregar la fecha/hora actual del cliente en formato ISO 8601
        config.headers['X-Client-Time'] = new Date().toISOString();
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

export default axios;
