import React from 'react';
import { Link, Navigate, Outlet } from 'react-router-dom';
import { useStateContext } from "../context/ContextProvider.jsx";
import { useNavigate } from 'react-router-dom';

export default function DefaultLayout() {
  const navigate = useNavigate();
  const { setUser, setToken, notification } = useStateContext();

  const onLogout = () => {
    localStorage.removeItem('adminToken');
    localStorage.removeItem('profile_id');
    localStorage.removeItem('username');

    navigate("/verifyAdmin");
  };

  const adminToken = localStorage.getItem('adminToken');
  if(!adminToken){
    return <Navigate to='/verifyAdmin' />
  }
  const username = localStorage.getItem('username');
  const profile_id = localStorage.getItem('profile_id');

  return (
    <div id='defaultLayout'>
      <aside>
        <div className='imgBox'><img className='w-5' src={`${import.meta.env.VITE_API_BASE_URL}/image/logo.png`} /></div>
        <Link to='/dashboard'>Dashboard</Link>
        <Link to='/user'>Pandit Verification</Link>
        <Link to='/Community'>Community Verification</Link>
        <Link to='/wishlist'>Wishlist</Link>
        <Link to='/business'>Business Verification</Link>
        <Link to='/green-tick'>User Verification</Link>
        <Link to='/package'>Package</Link>
        <Link to='/boost-request'>Boost Request</Link>
        <Link to='/reported-post'>Reported Post</Link>
        <Link to='/amenities'>Amenities</Link>
      </aside>
      <div className="content">
        <header>
          <div>{username}</div>
          <div>
            <button className='btn-custom' onClick={onLogout}>Logout</button> &nbsp; &nbsp;
          </div>
        </header>
        <main>
          <Outlet />
        </main>
        {notification &&
          <div className="notification">
            {notification}
          </div>
        }
      </div>
    </div>
  );
}
