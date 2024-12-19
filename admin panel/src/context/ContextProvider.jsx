import React from 'react'
import { createContext, useContext, useState } from "react";
  const stateContext = createContext({
    user: null,
    token: null,
    notification: null,
    setUser: () => {},
    setToken: () => {},
    setToken: () => {},
    setNotification: () => {}
})
export const ContextProvider = ({children}) => {
    const [user, setUser] = useState({});
    const [token, _setToken] = useState(localStorage.getItem('ACCESS_TOKEN'));
    const [notification, _setNotification] = useState('');
    
    const setToken = (token) => {
        _setToken(token);
        if(token){
            localStorage.setItem('ACCESS_TOKEN',token);
        }else {
            localStorage.removeItem('ACCESS_TOKEN');
        }
    }

    const setNotification = message => {
    _setNotification(message);

    setTimeout(() => {
      _setNotification('')
    }, 3000)
  }

    return (
    <stateContext.Provider value={{
        user,
        token,
        setUser,
        setToken,
        notification,
        setNotification,
    }}>
        {children}
    </stateContext.Provider>
    )
}

export const useStateContext = () => useContext(stateContext)