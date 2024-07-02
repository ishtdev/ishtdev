import React, { useRef, useState, useEffect } from 'react';
import axiosClient from "../axios-client.js";
import { Link } from "react-router-dom";
import { useStateContext } from "../context/ContextProvider.jsx";
import { useNavigate } from 'react-router-dom';

export default function VerifyOtp() {
  const navigate = useNavigate();
  const otpRef = useRef(null);
  const { setUser, setToken, setNotification } = useStateContext();
  const [message, setMessage] = useState(null);
  const [timer, setTimer] = useState(60);

  const clearMessage = () => {
    setMessage(null);
  };

  useEffect(() => {
    let interval;
    if (timer > 0) {
      interval = setInterval(() => {
        setTimer(prevTimer => prevTimer - 1);
      }, 1000);
    }

    return () => clearInterval(interval);
  }, [timer]); 

  const onSubmit = ev => {
    ev.preventDefault();
    const payload = {
      verification_code: otpRef.current.value,
    };
    axiosClient.post('/otpVerification', payload)
      .then(({ data }) => {
        if (data.code === 400) {
          setMessage('OTP is invalid.');
        } else if (data.code === 404) {
          setMessage('OTP is expired.');
        } else if (data.code === 200) {
          const { authorization } = data.data; 
          if (authorization) {
            setUser(data.data);
            localStorage.setItem('adminToken', authorization);
            localStorage.setItem('username', data.data.username);
            localStorage.setItem('profile_id', data.data.profileId);
            navigate("/dashboard");
          } else {
            console.error('Authorization token is missing in the response:', data);
          }
        } 
      })
      .catch((err) => {
        const response = err.response;
        if (response && response.status === 422) {
          setMessage(response.data.message);
        }
      });
  }

  const handleResendOTP = () => {
  setTimer(60);
  const mobileNumber = localStorage.getItem('mobile_number');
  if (mobileNumber) {
    const payload = {
      mobile_number: mobileNumber,
    };

    axiosClient.post('/verifyAdmin', payload)
      .then(({ data }) => {
        navigate("/verifyAdmin/otp");
       
      })
      .catch((err) => {
        const response = err.response;
        if (response && response.status === 422) {
          setMessage(response.data.errors);
          localStorage.removeItem('mobile_number');
        }
      });
  } else {
    setMessage('Mobile number not found.');
  }
};


  return (
    <div className="login-signup-form animated fadeInDown">
      <div className="form">
        <form onSubmit={onSubmit}>
          <div className='text-center mb-4'><img className='w-5' src={`${import.meta.env.VITE_API_BASE_URL}/image/logo.png`} /></div>
          <h1 className="title">Admin Panel</h1>
          <div className='title-subline text-center'>Verify OTP</div>
          {message &&
            <div className="alert">
              <p>{message}</p>
            </div>
          }
          
          <input className='formInput' ref={otpRef} maxLength={4} placeholder="Enter OTP" onFocus={clearMessage}/>
          <div className='button-box'>
            <button type="submit" className="btn-custom btn-block">Verify OTP</button>
            <div className="resend-container">
              <span className="text-black">Didn't receive OTP? </span><br />
              {timer > 0 ? (
                <span className="text-black">Resend OTP in {timer} seconds</span>
              ) : (
                <Link to="#" className="resend-link text-orange text-decoration-none" onClick={handleResendOTP}>Resend OTP</Link>
              )}
            </div>
          </div>
        </form>
      </div>
    </div>
  );
}