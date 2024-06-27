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
            setReason(data.data.length > 0 ? data.data[0].rejection_reason : '');
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


    const handleInputChange = (event) => {
        setReason(event.target.value);
    };

    const handleEditReason = () => {
        setErrors('');
        setModalIsOpen(true);
    };

    const handleSaveReason = () => {
        setCommunities([{ ...Communities[0], rejection_reason: reason }, ...Communities.slice(1)]);
        setModalIsOpen(false);
    };

    const handleSubmit = (ev) => {
        ev.preventDefault();
        if (Communities.length > 0 && Communities[0].id) {
            const updatedCommunity = {
                ...Communities[0],
                profile_id: profile_id,
                community_id: Communities[0].id
            };

            if ((Communities[0].status === "rejected" || Communities[0].status === "block") && !Communities[0].rejection_reason) {
                setErrors('Rejection Reason is Mandatory Field');      
            }else{
                setErrors('');
                axiosClient.post(`/addupdateCommunity`, updatedCommunity)
                .then(() => {
                    setNotification('Community successfully updated');
                    this.timer = setTimeout(() => {
                        window.location = '/community';
                      }, 1000);
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
                                <label>Main Festival</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={Communities.length > 0 && Communities[0].main_festival_community ? Communities[0].main_festival_community : 'Not Available'} readOnly/>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Lord Name</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={Communities.length > 0 && Communities[0].community_lord_name ? Communities[0].community_lord_name : 'Not Available'} readOnly />
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Schedual Visit</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={Communities.length > 0 && Communities[0].schedual_visit  ? Communities[0].schedual_visit : 'Not Available'} readOnly />
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Location</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={Communities.length > 0 && Communities[0].location_of_community ? Communities[0].location_of_community : 'Not Available'} readOnly/>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Distance From Main City</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={Communities.length > 0 && Communities[0].distance_from_main_city ? Communities[0].distance_from_main_city : 'Not Available'} readOnly />
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Distance From Airport</label>
                            </div>
                            <div className="col-lg-9">
                                <input readOnly value={Communities.length > 0 && Communities[0].distance_from_airpot ? Communities[0].distance_from_airpot : 'Not Available'} />
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Long Description</label>
                            </div>
                            <div className="col-lg-9">
                                <p readOnly >{Communities.length > 0 && Communities[0].long_description ? Communities[0].long_description : 'Not Available'}</p>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Gallery</label>
                            </div>
                             <div className="col-lg-9 d-flex mb-2">
                                    {Communities.length > 0 && Communities[0].upload_pdf ? (
                                        <a href={pdf} target="_blank" rel="noopener noreferrer" className='rounded highlight text-decoration-none default-bg-clr text-dark input-bdr p-1 mr-3'>View PDF</a>
                                    ) : (
                                        <a className='rounded text-decoration-none default-bg-clr text-dark input-bdr p-1 mr-3'>PDF Not Available</a>
                                    )}
                                    {Communities.length > 0 && Communities[0].upload_video ? (
                                        <a href={video} target="_blank" rel="noopener noreferrer" className='rounded highlight text-decoration-none default-bg-clr text-dark input-bdr p-1'>View Video</a>
                                    ) : (
                                        <a className='rounded text-decoration-none default-bg-clr text-dark input-bdr p-1'>Video Not Available</a>
                                    )}
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Documents</label>
                            </div>
                             <div className="col-lg-9 d-flex">
                                    {Communities.length > 0 && Communities[0].upload_licence01 ? (
                                        <a href={Licence_front} target="_blank" rel="noopener noreferrer" className='rounded text-decoration-none default-bg-clr text-white p-1 mr-3 highlight'>View Licence Front</a>
                                    ) : (
                                        <a className='rounded text-decoration-none default-bg-clr text-dark input-bdr p-1 mr-3'>Licence Front Not Available</a>
                                    )}
                                    {Communities.length > 0 && Communities[0].upload_licence02 ? (
                                        <a href={Licence_back} target="_blank" rel="noopener noreferrer" className='rounded highlight text-decoration-none default-bg-clr text-white p-1 mr-3'>View Licence Back</a>
                                    ) : (
                                        <a className='rounded text-decoration-none default-bg-clr text-dark input-bdr p-1 mr-3'>Licence Back Not Available</a>
                                    )}
                                    {Communities.length > 0 && Communities[0].upload_qr ? (
                                        <a href={qr} target="_blank" rel="noopener noreferrer" className='rounded highlight text-decoration-none default-bg-clr text-white p-1'>View QR</a>
                                    ) : (
                                        <a className='rounded text-decoration-none default-bg-clr text-dark input-bdr p-1'>QR Not Available</a>
                                    )}
                            </div>
                        </div>
                        <div className="row mt-3 mb-3 ">
                            <div className="col-lg-3">
                                <label>Facilities</label>
                            </div>
                            <div className="col-lg-9 d-flex">
                                {Communities.length > 0 && Communities[0].facility ? (
                                    Object.keys(Communities[0].facility).map((facilityType, index) => (
                                        <table className='facility-bx mr-2 col-lg-3'>
                                            <div key={index} className='p-2'>
                                                <tr className='table-head text-center fw-bold p-1'>{capitalizeFirstLetter(facilityType)}</tr>
                                                {Communities[0].facility[facilityType].map((facility, idx) => (
                                                    <tr key={idx}> 
                                                        <td>{facility.key}</td>
                                                        <td>{facility.value}</td>
                                                    </tr>
                                                ))}
                                            </div>
                                        </table>
                                    ))
                                ) : (
                                    <span>Not Available</span>
                                )}
                            </div>
                        </div>
                        <div className="row mt-3 mb-3">
                            <div className="col-lg-3">
                                <label>Timing Of Temple</label>
                            </div>
                            <div className="col-lg-9 d-flex">
                                {Time.length > 0 ? (
                                             <div className="col-lg-3 d-flex facility-bx">
                                    <table>
                                        <thead>
                                            <tr className="d-flex">
                                                <th className='table-head text-center fw-bold p-1 mr-1'>Open Time</th>
                                                <th className='table-head text-center fw-bold p-1'>Close Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {Time.map((timeEntry, index) => (
                                                <tr key={index} className="d-flex">
                                                    <td className="p-1 w-50 text-center mr-1">{timeEntry.open_time}</td>
                                                    <td className="p-1 w-50 text-center">{timeEntry.close_time}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                    </div>
                                ) : (
                                    <span>Not Available</span>
                                )}
                            </div>
                        </div>
                        <input type='hidden' value={Communities.length > 0 ? Communities[0].id : ''} onChange={ev => setCommunities([{ ...Communities[0], id: ev.target.value }, ...Communities.slice(1)])} placeholder="Profile Id" />
                        <input type='hidden' value={Communities.length > 0 ? Communities[0].profile_id : ''} onChange={ev => setCommunities([{ ...Communities[0], profile_id: ev.target.value }, ...Communities.slice(1)])} placeholder="Profile Id" />
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Status</label>
                            </div>
                            <div className="col-lg-9">
                                <select className='input-border' value={Communities.length > 0 ? Communities[0].status : ''} onChange={ev => {
                                        const selectedValue = ev.target.value;
                                        setCommunities([{ ...Communities[0], status: selectedValue }, ...Communities.slice(1)]);
                                        setReason(selectedValue === 'rejected' || selectedValue === 'block' ? Communities[0].rejection_reason : ''); // Update reason based on status
                                        setModalIsOpen(selectedValue === 'rejected' || selectedValue === 'block');
                                }}>
                                    <option disabled value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="approved_with_tick">Approve With Tick</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="block">Block</option>
                                </select> 
                            </div>
                        </div>
                        {Communities.length > 0 && (Communities[0].status === "rejected" || Communities[0].status === "block") && (
                            <div className="row">
                                <div className="col-lg-3">
                                    <label>Reason</label>
                                </div>
                                <div className="col-lg-9">
                                    {errors &&
                                        <div className="alert">
                                                <p>{errors}</p>
                                        </div>
                                    }
                                    <input className='input-bdr w-8' required value={Communities[0].rejection_reason} readOnly placeholder="Reason" />
                                    <button type="button" onClick={handleEditReason} className="btn-custom btn-reason">Edit Reason</button>
                                </div>
                            </div>
                        )}
                        <Modal isOpen={modalIsOpen} onRequestClose={() => setModalIsOpen(false)} contentLabel="Reason Popup">
                            <h2 className='text-center'>Add Reason</h2>
                            <input required className='btn-block input-bdr' value={reason} onChange={handleInputChange} placeholder="Reason" />
                            <div className='text-end'>
                                <button className={`btn-custom mr-1 ${(!reason || !reason.trim()) && 'disabled'}`} onClick={handleSaveReason} disabled={!reason || !reason.trim()}>Save</button>
                                <button className="btn-custom" onClick={() => setModalIsOpen(false)}>Close</button>
                            </div>
                        </Modal>
                        <button type="submit" className="btn-custom">Update</button>
                    </form>
                )}
            </div>
        </>
    );
}
