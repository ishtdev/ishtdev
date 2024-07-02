import React, { useRef, useState } from 'react';
import { Link } from "react-router-dom";
import axiosClient from "../axios-client.js";
import { useStateContext } from "../context/ContextProvider.jsx";
import { useNavigate } from 'react-router-dom';

export default function VerifyAdmin() {
  const naviagte = useNavigate();
  const numRef = useRef(null);
  const { setUser, setNotification } = useStateContext();
  const [message, setMessage] = useState(null);
  const [errors, setErrors] = useState(null);

  const clearMessage = () => {
    setMessage(null);
  };

  const onSubmit = ev => {
    ev.preventDefault();

    const mobileNumber = numRef.current.value;
    
    const isValidMobileNumber = /^[6-9]\d{9}$/.test(mobileNumber);
    if (!isValidMobileNumber) {
      setMessage('Mobile Number is invalid.');
      return;
    }

    localStorage.setItem('mobile_number', mobileNumber);
      
    const payload = {
      mobile_number: mobileNumber,
    };

    axiosClient.post('/verifyAdmin', payload)
      .then(({ data }) => {
        if (data.code === 404) {
          setMessage('Admin is not registered.');
        }else{
        naviagte("/verifyAdmin/otp");
        setUser(data.user);
        }
      })
      .catch((err) => {
        const response = err.response;
        if (response && response.status === 422) {
          setErrors(response.data.errors);
        }
      });
  };

  return (
    <div className="login-signup-form animated fadeInDown">
      <div className="form">
        <form onSubmit={onSubmit}>
          <div className='text-center mb-4'><img className='w-5' src={`${import.meta.env.VITE_API_BASE_URL}/image/logo.png`} /></div>
          <h1 className="title">Admin Panel</h1>
          <div className='title-subline text-center'>Login</div>
          {message && (
            <div className="alert">
              <p>{message}</p>
            </div>
          )}
          <label>Mobile Number</label>
          <input className='formInput' ref={numRef} type="tel" maxLength={10} placeholder="Enter Mobile Number" onFocus={clearMessage} />
            <div className='button-box'>
              <button className="btn-custom btn-block">Send OTP</button>
          </div>
          {/* <p className="message">Not registered? <Link to="/signup">Create an account</Link></p> */}
        </form>
      </div>
    </div>
  );
}
