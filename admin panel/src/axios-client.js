import axios from "axios";
import { useStateContext } from "./context/ContextProvider.jsx";

const axiosClient = axios.create({
  baseURL: `${import.meta.env.VITE_API_BASE_URL}/api`
});

axiosClient.interceptors.request.use(
  config => {
    const adminToken = localStorage.getItem('adminToken');
    if (adminToken) {
      config.headers.Authorization = `Bearer ${adminToken}`;
    }
    return config;
  },
  error => {
    return Promise.reject(error);
  }
);

export const login = async (credentials) => {
  try {
    const response = await axiosClient.post('/login', credentials);
    const { data } = response;
    const { token } = data; 

    const { setToken } = useStateContext();
    setToken(token);

    return data; 
  } catch (error) {
    throw error;
  }
};

export default axiosClient;
