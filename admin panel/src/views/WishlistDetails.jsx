import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import axiosClient from '../axios-client.js';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faMale, faFemale, faChild } from '@fortawesome/free-solid-svg-icons';

export default function WishlistDetails() {
  const { wishlist_id } = useParams();
  const [wishlist, setWishlist] = useState({});
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    getWishlist();
  }, [wishlist_id]);

  const getWishlist = () => {
    setLoading(true);
    axiosClient
      .get(`/wishlist-detail/${wishlist_id}`)
      .then(({ data }) => {
        setLoading(false);
        setWishlist(data.data);
      })
      .catch(() => {
        setLoading(false);
      });
  };

  const profile_picture = `${import.meta.env.VITE_API_BASE_URL}/${wishlist?.user_detail?.profile_picture || ''}`;
  const dummy_image = `${import.meta.env.VITE_API_BASE_URL}/communitydocument/dummy-profile-pic.jpg`;

  return (
    <>
      <div className="card animated fadeInDown">
        {loading && <div className="text-center">Loading...</div>}
        {!loading && (
          <>
            <div className="row pb-5">
              <div className="col-lg-2">
                {wishlist?.user_detail?.profile_picture ? (
                  <a href={profile_picture} target="_blank" rel="noopener noreferrer">
                    <img className='profile_picture' src={profile_picture} alt="User Profile" />
                  </a>
                ) : (
                  <img className='profile_picture' src={dummy_image} alt="Dummy Image" />
                )}
              </div>
              <div className="col-lg-10">
                <h2 className='pt-4'>{wishlist?.user_detail?.full_name}</h2>
                <p className='default-txt-clr'>{wishlist?.user_detail?.email}</p>
              </div>
            </div>
            <div className="row">
                <div className="col-lg-2">
                    <label>Title</label>
                </div>
                <div className="col-lg-9">
                    <p className='default-txt-clr'>{wishlist?.title}</p>
                </div>
            </div>
            <div className="row">
                <div className="col-lg-2">
                    <label>Planning On</label>
                </div>
                <div className="col-lg-9">
                    <p className='default-txt-clr'>{wishlist?.date}</p>
                </div>
            </div>
            <div className="row">
                <div className="col-lg-2">
                    <label>Planning With</label>
                </div>
                <div className="col-lg-9">
                    <p className='default-txt-clr'>{wishlist?.planning_with}</p>
                </div>
            </div>
            <div className="row">
                <div className="col-lg-2">
                    <label>Total Member</label>
                </div>
                <div className="col-lg-9">
                    <p className='default-txt-clr'>{wishlist?.total_member}</p>
                </div>
            </div>
            <div className="row">
                <div className="col-lg-2">
                    <label>People Ratio</label>
                </div>
                <div className="col-lg-9">
                    <span className='default-txt-clr'>
                        <FontAwesomeIcon icon={faMale} style={{ color: 'blue' }}/>
                    </span>
                    <span className='default-txt-clr ml-1 mr-3'>
                    {wishlist?.num_of_male}
                    </span>
                    <span className='default-txt-clr'>
                        <FontAwesomeIcon icon={faFemale} style={{ color: '#df6cd4' }}/>
                    </span>
                    <span className='default-txt-clr ml-1 mr-3'>
                    {wishlist?.num_of_female}
                    </span>
                    <span className='default-txt-clr'>
                        <FontAwesomeIcon icon={faChild} style={{ color: 'orange' }}/>
                    </span>
                    <span className='default-txt-clr ml-1 mr-3'>
                    {wishlist?.num_of_child}
                    </span>
                </div>
            </div>
          </>
        )}
      </div>
    </>
  );
}
