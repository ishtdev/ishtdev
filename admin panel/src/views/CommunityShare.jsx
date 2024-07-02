import React, { useEffect, useState } from 'react';
import { useParams } from "react-router-dom";
import axiosClient from "../axios-client.js";

export default function CommunityShare() {
    let { profile_id } = useParams();
    const [errors, setErrors] = useState(null);
    const [Profile, setProfile] = useState({});
    const [History, setHistory] = useState([]);
    const [loading, setLoading] = useState(false);
    
    useEffect(() => {
        getProfile();
    }, []);

    const getProfile = () => {
        setLoading(true);
        axiosClient.post(`/userProfile/${profile_id}`)
            .then(({ data }) => {
                setLoading(false);
                setProfile(data.data);
                getProfileHistory(data.data.userDetails.id);
            })
            .catch(err => {
                const response = err.response;
                setLoading(false);
                if (response && response.status === 422) {
                    setErrors(response.data.errors);
                }
            });
    };

    const getProfileHistory = (userId) => {
        setLoading(true);
        axiosClient.get(`/CommunityHistory/${userId}`)
            .then(({ data }) => {
                setLoading(false);
                setHistory(data.data);
            })
            .catch(err => {
                const response = err.response;
                setLoading(false);
                if (response && response.status === 422) {
                    setErrors(response.data.errors);
                }
            });
    };

    const community_image = Profile.userDetails && Profile.userDetails.community_image ? `${import.meta.env.VITE_API_BASE_URL}/${Profile.userDetails.community_image}` : '';
    const dummy_image = `${import.meta.env.VITE_API_BASE_URL}/communitydocument/dummy-profile-pic.jpg`;
    const logo = `${import.meta.env.VITE_API_BASE_URL}/image/logo.png`;

    return (
        <section class="bg-image">
          <div className='container position-absolute'>
                <div className='text-center p-3'>
                  <img src={logo} alt="logo" className='width-50 w-1 mb-4'/>
                </div>
            {Profile.userDetails && (
                <div class="text-center">
                  {community_image ? (
                      <a href={community_image} target="_blank" rel="noopener noreferrer">
                          <img className='post-image' src={community_image} />
                      </a>
                  ) : (
                      <img className='post-image' src={dummy_image}/>
                  )}
                  <h4 className='mt-2'>{Profile.userDetails.name_of_community}</h4>
                  <p className='display-block text-color text-dark mb-0'>{Profile.userDetails.short_description}</p>
                  <p className='display-block text-color text-dark mb-0 mt-3'><strong>{Profile.countFollower ? `${Profile.countFollower} Likes` : '0 Likes'}</strong></p>
                  <p className='display-block text-color text-dark '><strong>{Profile.postCount ? `${Profile.postCount} Comments` : '0 Comments'}</strong></p>
                  {History.length > 0 && (
                        <div>
                            <h6>History of Community</h6>
                            <ul className='list-style-none padding-left-0'>
                                {History.map((historyEntry, index) => (
                                    <li key={index}>{historyEntry.history}</li>
                                ))}
                            </ul>
                        </div>
                    )}
                  <a href='https://drive.google.com/file/d/13LzKaM6m5qaBXP14K5fqsHvPjhFWwEC0/view?usp=drive_link' rel="noopener noreferrer" target='_blank'><button class="btn-custom">Downlaod the App Now</button></a>
                </div>

                
            )}
            {errors && <p>Error: {errors}</p>}
        </div>
        </section>
    );
}
