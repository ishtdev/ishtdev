import React, { useEffect, useState } from 'react';
import { Link, useParams } from "react-router-dom";
import axiosClient from "../axios-client.js";
import { useStateContext } from "../context/ContextProvider.jsx";
import Modal from 'react-modal';
import { FaExternalLinkAlt } from 'react-icons/fa';

export default function CommunityDetails() {
    let { profile_id } = useParams();
    const [modalIsOpen, setModalIsOpen] = useState(false);
   const [reason, setReason] = useState('');
   const [errors, setErrors] = useState(null);
    const { setNotification } = useStateContext()
    const [Communities, setCommunities] = useState([]);
    const [Time, setTime] = useState([]);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        getCommunities();
    }, []);

    const getCommunities = () => {
        setLoading(true);
        axiosClient.get(`/showCommunityDetails/${profile_id}`)
            .then(({ data }) => {
                setLoading(false);
                setCommunities(data.data);
                setReason(data.data.length > 0 ? data.data[0].mobile_number : '');
                axiosClient.get(`/showCommunityTime/${profile_id}`)
                    .then(({ data: timeData }) => {
                        setTime(timeData.data);
                    })
                    .catch(err => {
                        const response = err.response;
                        setLoading(false);
                        if (response && response.status === 422) {
                            setErrors(response.data.errors);
                        }
                    });
            })
            .catch(err => {
                const response = err.response;
                setLoading(false);
                if (response && response.status === 422) {
                    setErrors(response.data.errors);
                }
            });
    };

    const handleSubmit = (ev) => {
        ev.preventDefault();
         const formErrors = {};
    if (Communities.length > 0 && Communities[0].id) {
            const updatedCommunity = {
             ...Communities[0],
                profile_id: profile_id,
         community_id: Communities[0].id,
                 mobile_number:mobile_number
                
            };
         const formData = new FormData(ev.target);
        const updatedAmenity = Object.fromEntries(formData.entries());

        if (!updatedAmenity.mobile_number?.trim()) {
            formErrors.mobile_number = "Mobile Number is required.";
        }

           if (Object.keys(formErrors).length > 0) {
            setErrors(formErrors);
            return;
        } else {
            setErrors({});
            setLoading(true);

            axiosClient.post(`/transferCommunity`, updatedAmenity)
                .then(() => {
                    setNotification('Community Transfer successfull');
                    setTimeout(() => {
                        window.location = '/communityTransfer';
                    }, 1000);
                    setLoading(false);
                })
                .catch(err => {
                    const response = err.response;
                    setLoading(false);
                    if (response && response.status === 422) {
                        setErrors(response.data.errors);
                    }
                });
        }
    }
    };
  function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    const community_background_image = `${import.meta.env.VITE_API_BASE_URL}/${Communities.length > 0 ? Communities[0].community_background_image : ''}`;
    const community_image = `${import.meta.env.VITE_API_BASE_URL}/${Communities.length > 0 ? Communities[0].community_image : ''}`;
    const dummy_background_image = `${import.meta.env.VITE_API_BASE_URL}/communitydocument/banner-image.png`;
    const dummy_image = `${import.meta.env.VITE_API_BASE_URL}/communitydocument/dummy-profile-pic.jpg`;
    const qr = `${import.meta.env.VITE_API_BASE_URL}/${Communities.length > 0 ? Communities[0].upload_qr : ''}`;
    const Licence_front = `${import.meta.env.VITE_API_BASE_URL}/${Communities.length > 0 ? Communities[0].upload_licence01 : ''}`;
    const Licence_back = `${import.meta.env.VITE_API_BASE_URL}/${Communities.length > 0 ? Communities[0].upload_licence02 : ''}`;
    const video = `${import.meta.env.VITE_API_BASE_URL}/${Communities.length > 0 ? Communities[0].upload_video : ''}`;
    const pdf = `${import.meta.env.VITE_API_BASE_URL}/${Communities.length > 0 ? Communities[0].upload_pdf : ''}`;

    

    return (
        <>
            <div className="card animated fadeInDown">
                {loading && (
                    <div className="text-center">
                        Loading...
                    </div>
                )}
                {!loading && (
                    <form onSubmit={handleSubmit} >
                      
                        <div className="row">
                            <div className="col-lg-12">
                                {Communities.length > 0 && Communities[0].community_background_image ? (
                                    <div>
                                        <img className='banner_image' src={Communities[0].community_background_image} alt="Community Background" />
                                        <a className='open-icon' href={Communities[0].community_background_image} target="_blank" rel="noopener noreferrer">
                                            <FaExternalLinkAlt /> {/* Icon */}
                                        </a>
                                    </div>
                                ) : (
                                    <img className='banner_image' src={dummy_background_image} alt="Dummy Image" />
                                )}
                            </div>
                        </div>
                        <div className="row pb-5">
                            <div className="col-lg-3">
                                {Communities.length > 0 && Communities[0].community_image ? (
                                    <a href={community_image} target="_blank" rel="noopener noreferrer">
                                        <img className='profile_image' src={community_image} value={Communities.length > 0 ? Communities[0].name_of_community : ''} readOnly placeholder="NULL" />
                                    </a>
                                ) : (
                                    <img className='profile_image' src={dummy_image} alt="Dummy Image" />
                                )}
                            </div>
                            <div className="col-lg-9">
                                <h2 className='pt-4' readOnly >{Communities.length > 0 && Communities[0].name_of_community ? Communities[0].name_of_community : ''}</h2>
                                <p className='default-txt-clr' readOnly >{Communities.length > 0 && Communities[0].short_description ? Communities[0].short_description : ''}</p>
                            </div>
                        </div>
                         <div className="row">
                        
                        <div className="col-lg-3">
                            <label htmlFor='Communities'> Enter User Phone</label>
                        </div>
                         <div className="col-lg-9">
                            <input
    type="hidden"
    className="package-width input-border"
    id="community_id"
    name="community_id"
    value={Communities.length > 0 && Communities[0]?.id ? Communities[0].id : ''}
    readOnly
/>
                                <input
    type="hidden"
    className="package-width input-border"
    id="profile_id"
    name="profile_id"
    value={Communities.length > 0 && Communities[0]?.profile_id ? Communities[0].profile_id : ''}
    readOnly
/>


                            <input type='number' className='package-width input-border' id='mobile_number' name='mobile_number' placeholder='Enter phone number' />
                        </div>
                    </div>
                        
                        <button type="submit" className="btn-custom">Update</button>
                    </form>
                )}
            </div>
        </>
    );
}
