import React, { useEffect, useState } from 'react';
import { redirect, useParams } from "react-router-dom";
import axiosClient from "../axios-client.js";
import { useStateContext } from "../context/ContextProvider.jsx";
import Modal from 'react-modal';

export default function BusinessDetails() {
    let { profile_id } = useParams();
    const [modalIsOpen, setModalIsOpen] = useState(false);
    const [business_invalidate_reason, setReason] = useState('');
    const [errors, setErrors] = useState(null);
    const { setNotification } = useStateContext()
    const [user, setUser] = useState({});
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        getBusiness();
    }, []);

    const getBusiness = () => {
        setLoading(true);
        axiosClient.post(`/userProfile/${profile_id}`)
            .then(({ data }) => {
                setLoading(false);
                setUser(data.data.userDetails);
                setReason(data.data.userDetails.business_invalidate_reason || '');
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
        setUser({ ...user, business_invalidate_reason: business_invalidate_reason });
        setModalIsOpen(false);
    };

    const handleSubmit = (ev) => {
        ev.preventDefault();
        if (user.id) {
            const updatedUser = {
                ...user,
                profile_id: profile_id,
                user_id: user.id,
                business_verification_status: user.business_verification_status
            };

            if ((user.business_verification_status === "rejected") && !user.business_invalidate_reason) {
                setErrors('Rejection Reason is Mandatory Field');
            } else {
                setErrors('');

                if (updatedUser?.business_verification_status === 'pending') {
                    alert("Can't' update status as 'pending'!");
                    return;
                } else {
                    axiosClient.post(`/profiles`, updatedUser)
                        .then(() => {
                            setNotification('Business profile successfully updated');
                            this.timer = setTimeout(() => {
                                window.location = '/business';
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
        }
    };

    const profile_picture = `${import.meta.env.VITE_API_BASE_URL}/${user.profile_picture || ''}`;
    const dummy_image = `${import.meta.env.VITE_API_BASE_URL}/communitydocument/dummy-profile-pic.jpg`;
    const document = `${import.meta.env.VITE_API_BASE_URL}/${user.business_doc ? user.business_doc : ''}`;
    const fileName = user?.business_doc?.split('/').pop();
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
                                <label>Business Type</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={user.business_type ? user.business_type : 'Not Available'} readOnly />
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Business Name</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={user.business_name ? user.business_name : 'Not Available'} readOnly />
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Verified</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={user.is_business_profile ? user.is_business_profile : 'Not Available'} readOnly />
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                            <label>File Name</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={user.business_doc ? user.business_doc.split('/').pop() : 'Not Available'} readOnly />
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Document</label>
                            </div>
                            <div className="col-lg-9 d-flex">
                                {
                                    user.business_doc ? (

                                        <a href={document} target="_blank" rel="noopener noreferrer" className='text-decoration-none default-bg-clr text-dark input-bdr p-1 mr-3'>View Document</a>
                                    ) : (
                                        <a className='text-decoration-none default-bg-clr text-dark input-bdr p-1 mr-3'>Document Not Available</a>
                                    )}
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>GST Number</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={user.gst_number ? user.gst_number : 'Not Available'} readOnly />
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Business State</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={user.business_state ? user.business_state : 'Not Available'} readOnly />
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Business City</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={user.business_city ? user.business_city : 'Not Available'} readOnly />
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Business Pincode</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={user.business_pincode ? user.business_pincode : 'Not Available'} readOnly />
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-lg-3">
                                <label>Business Address</label>
                            </div>
                            <div className="col-lg-9">
                                <input value={user.business_address ? user.business_address : 'Not Available'} readOnly />
                            </div>
                        </div>
                        <input type='hidden' value={user.id ? user.id : ''} onChange={ev => setUser([{ ...user, id: ev.target.value }, ...user.slice(1)])} placeholder="Profile Id" />
                        <input type='hidden' value={user.profile_id ? user.profile_id : ''} onChange={ev => setUser([{ ...user, profile_id: ev.target.value }, ...user.slice(1)])} placeholder="Profile Id" />

                        <div className="row">
                            <div className="col-lg-3">
                                <label>Status</label>
                            </div>
                            <div className="col-lg-9">
                                <select className='input-border' value={user.business_verification_status || ''} onChange={ev => {
                                    const selectedValue = ev.target.value;
                                    setUser({ ...user, business_verification_status: selectedValue });
                                    setReason(selectedValue === 'rejected' ? user.business_invalidate_reason : ''); // Update reason based on status
                                    setModalIsOpen(selectedValue === 'rejected');
                                }}>
                                    <option disabled value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                        {(user.business_verification_status === "rejected") && (
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
                                    <input className='input-bdr w-8' required value={user.business_invalidate_reason || ''} onChange={handleInputChange} placeholder="Reason" disabled />
                                    <button type="button" onClick={handleEditReason} className="btn-custom btn-reason">Edit Reason</button>
                                </div>
                            </div>
                        )}
                        <Modal isOpen={modalIsOpen} onRequestClose={() => setModalIsOpen(false)} contentLabel="Reason Popup">
                            <h2 className='text-center'>Add Reason</h2>
                            <input required className='btn-block input-bdr' value={business_invalidate_reason} onChange={handleInputChange} placeholder="Reason" />
                            <div className='text-end'>
                                <button className={`btn-custom mr-1 ${(!business_invalidate_reason || !business_invalidate_reason.trim()) && 'disabled'}`} onClick={handleSaveReason} disabled={!business_invalidate_reason || !business_invalidate_reason.trim()}>Save</button>
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
