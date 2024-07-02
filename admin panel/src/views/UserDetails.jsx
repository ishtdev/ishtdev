import React, { useEffect, useState } from 'react';
import { useParams } from "react-router-dom";
import axiosClient from "../axios-client.js";
import { useStateContext } from "../context/ContextProvider.jsx";
import Modal from 'react-modal';

export default function UserDetails() {
    let { profile_id } = useParams();
    const [modalIsOpen, setModalIsOpen] = useState(false);
    const [reason, setReason] = useState('');
    const [errors, setErrors] = useState(null);
    const { setNotification } = useStateContext()
    const [user, setUser] = useState({});
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        getCommunities();
    }, []);

    const getCommunities = () => {
        setLoading(true);
        axiosClient.post(`/userProfile/${profile_id}`)
            .then(({ data }) => {
                setLoading(false);
                setUser(data.data.userDetails);
                setReason(data.data.userDetails.rejection_reason || '');
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
        setUser({ ...user, rejection_reason: reason });
        setModalIsOpen(false);
    };

    const handleSubmit = (ev) => {
        ev.preventDefault();
        if (user.id) {
            const updatedUser = {
                ...user,
                profile_id: profile_id,
                user_id: user.id,
                become_pandit: user.become_pandit
            };

            if ((user.become_pandit === "rejected" || user.become_pandit === "block") && !user.rejection_reason) {
                setErrors('Rejection Reason is Mandatory Field');
            } else {
                setErrors('');
                axiosClient.post(`/updateUserType`, updatedUser)
                .then(() => {
                    setNotification('User successfully updated');
                    this.timer = setTimeout(() => {
                        window.location = '/user';
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

    const profile_picture = `${import.meta.env.VITE_API_BASE_URL}/${user.profile_picture || ''}`;
    const dummy_image = `${import.meta.env.VITE_API_BASE_URL}/communitydocument/dummy-profile-pic.jpg`;
    const kyc_front = `${import.meta.env.VITE_API_BASE_URL}/${user.kyc_details_doc01 ? user.kyc_details_doc01 : ''}`;
    const kyc_back = `${import.meta.env.VITE_API_BASE_URL}/${user.kyc_details_doc01 ? user.kyc_details_doc02 : ''}`;

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
                        <div className="row pb-5">
                            <div className="col-lg-2">
                                {user.profile_picture ? (
                                    <a href={profile_picture} target="_blank" rel="noopener noreferrer">
                                        <img className='profile_picture' src={profile_picture} alt="User Profile" />
                                    </a>
                                ) : (
                                    <img className='profile_picture' src={dummy_image} alt="Dummy Image" />
                                )}
                            </div>
                            <div className="col-lg-10">
                                <h2 className='pt-4'>{user.full_name}</h2>
                                <p className='default-txt-clr'>{user.bio}</p>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Email ID</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={user.email ? user.email : 'Not Available'} readOnly/>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Date of Birth</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={user.dob ? user.dob : 'Not Available'} readOnly/>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Religion</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={user.religion ? user.religion : 'Not Available'} readOnly/>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Gotra</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={user.gotra ? user.gotra : 'Not Available'} readOnly/>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Varna</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={user.varna ? user.varna : 'Not Available'} readOnly/>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Ishtdev</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={user.ishtdev ? user.ishtdev : 'Not Available'} readOnly/>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Speciality Pooja</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={user.speciality_pooja ? user.speciality_pooja : 'Not Available'} readOnly/>
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>KYC Documents</label>
                            </div>
                             <div className="col-lg-9 d-flex">
                                    {user.kyc_details_doc01 ? (
                                        <a href={kyc_front} target="_blank" rel="noopener noreferrer" className='text-decoration-none default-bg-clr text-dark input-bdr p-1 mr-3'>View KYC Document Front</a>
                                    ) : (
                                        <a className='text-decoration-none default-bg-clr text-dark input-bdr p-1 mr-3'>KYC Front Document Not Available</a>
                                    )}
                                    {user.kyc_details_doc02 ? (
                                        <a href={kyc_back} target="_blank" rel="noopener noreferrer" className='text-decoration-none default-bg-clr text-dark input-bdr p-1 mr-3'>View KYC Document Back</a>
                                    ) : (
                                        <a className='text-decoration-none default-bg-clr text-dark input-bdr p-1 mr-3'>KYC Back Document Not Available</a>
                                    )}
                            </div>
                        </div>
                        <input type='hidden' value={user.id ? user.id : ''} onChange={ev => setUser([{ ...user, id: ev.target.value }, ...user.slice(1)])} placeholder="Profile Id" />
                        <input type='hidden' value={user.profile_id ? user.profile_id : ''} onChange={ev => setUser([{ ...user, profile_id: ev.target.value }, ...user.slice(1)])} placeholder="Profile Id" />
                      
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Status</label>
                            </div>
                            <div className="col-lg-9 mt-2">
                                <select className='input-border' value={user.become_pandit || ''} onChange={ev => {
                                    const selectedValue = ev.target.value;
                                    setUser({ ...user, become_pandit: selectedValue });
                                    setReason(selectedValue === 'rejected' || selectedValue === 'block' ? user.rejection_reason : ''); // Update reason based on status
                                    setModalIsOpen(selectedValue === 'rejected' || selectedValue === 'block');
                                }}>
                                    <option disabled value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="block">Block</option>
                                </select>
                            </div>
                        </div>
                        {(user.become_pandit === "rejected" || user.become_pandit === "block") && (
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
                                    <input className='input-bdr w-8' required value={user.rejection_reason || ''} onChange={handleInputChange} placeholder="Reason" />
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
