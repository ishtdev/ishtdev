import React, { useEffect, useState } from 'react';
import { useParams } from "react-router-dom";
import axiosClient from "../axios-client.js";
import { useStateContext } from "../context/ContextProvider.jsx";
import Modal from 'react-modal';

export default function BoostRequestDetails() {
    let { boost_id } = useParams();
    const [modalIsOpen, setModalIsOpen] = useState(false);
    const [rejected_reason, setReason] = useState('');
    const [errors, setErrors] = useState(null);
    const { setNotification } = useStateContext();
    const [boost, setBoost] = useState(null);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        getBoost();
    }, []);

    const getBoost = () => {
        setLoading(true);
        axiosClient.get(`/boost-request-detail/${boost_id}`)
            .then(({ data }) => {
                setLoading(false);
                if (data.data.length > 0) {
                    setBoost(data.data[0]);
                    setReason(data.data[0].rejected_reason || '');
                }
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
        setBoost({ ...boost, rejected_reason: rejected_reason });
        setModalIsOpen(false);
    };

    const handleSubmit = (ev) => {
        ev.preventDefault();
        if (boost.id) {
            const updatedBoost = {
                ...boost,
                boost_id: boost.id,
                status: boost.status
            };

            if (boost.status === "rejected" && !boost.rejected_reason) {
                setErrors('Rejection Reason is a mandatory field');
            } else {
                setErrors('');
                if (boost?.status === 'pending') {
                    alert("Can't' update status as 'pending'!");
                    return;
                } else {
                    axiosClient.post(`/change-status`, updatedBoost)
                        .then(() => {
                            setNotification('Boost Status updated successfully');
                            this.timer = setTimeout(() => {
                                window.location = '/boost-request';
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

    const profile_picture = `${import.meta.env.VITE_API_BASE_URL}/${boost?.profile_detail?.profile_picture || ''}`;
    const dummy_image = `${import.meta.env.VITE_API_BASE_URL}/communitydocument/dummy-profile-pic.jpg`;

    return (
        <>
            <div className="card animated fadeInDown">
                {loading && (
                    <div className="text-center">
                        Loading...
                    </div>
                )}
                {!loading && boost && (
                    <form onSubmit={handleSubmit}>
                        <input type='hidden' value={boost.id || ''} onChange={ev => setBoost({ ...boost, id: ev.target.value })} placeholder="Profile Id" />

                        <div className="row">
                            <div className="col-lg-12 pb-3">
                                <h3>User Details</h3>
                            </div>
                            <div className="col-lg-2">
                                {boost.profile_detail?.profile_picture ? (
                                    <a href={profile_picture} target="_blank" rel="noopener noreferrer">
                                        <img className='profile_picture' src={profile_picture} alt="User Profile" />
                                    </a>
                                ) : (
                                    <img className='profile_picture' src={dummy_image} alt="Dummy Image" />
                                )}
                            </div>
                            <div className="col-lg-10">
                                <div className="row">
                                    <div className="col-lg-3">
                                        <label>Full Name</label>
                                    </div>
                                    <div className="col-lg-9">
                                        <input value={boost.profile_detail?.full_name ? boost.profile_detail?.full_name : 'Not Available'} readOnly />
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-lg-3">
                                        <label>Bio</label>
                                    </div>
                                    <div className="col-lg-9">
                                        <input value={boost.profile_detail?.bio ? boost.profile_detail?.bio : 'Not Available'} readOnly />
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-lg-3">
                                        <label>Become Pandit</label>
                                    </div>
                                    <div className="col-lg-9">
                                        <input value={boost.profile_detail?.become_pandit ? boost.profile_detail?.become_pandit : 'Not Available'} readOnly />
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-lg-3">
                                        <label>Green Tick Verified</label>
                                    </div>
                                    <div className="col-lg-9">
                                        <input value={boost.profile_detail?.verified ? boost.profile_detail?.verified : 'Not Available'} readOnly />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="row pb-3">
                            <div className="col-lg-12 pb-3">
                                <h3>Post Details</h3>
                            </div>
                            <div className="col-lg-2">
                                {boost.post_detail?.post_related_data[0]?.post_data ? (
                                    <a href={`${import.meta.env.VITE_API_BASE_URL}/${boost.post_detail.post_related_data[0].post_data}`} target="_blank" rel="noopener noreferrer">
                                        <img className='profile_picture' src={`${import.meta.env.VITE_API_BASE_URL}/${boost.post_detail.post_related_data[0].post_data}`} alt="Post Image" />
                                    </a>
                                ) : (
                                    <img className='profile_picture' src={dummy_image} alt="Dummy Image" />
                                )}
                            </div>
                            <div className="col-lg-10">
                                <div className="row">
                                    <div className="col-lg-3">
                                        <label>Post Type</label>
                                    </div>
                                    <div className="col-lg-9">
                                        <input value={boost.post_detail?.post_type ? boost.post_detail?.post_type : 'Not Available'} readOnly />
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-lg-3">
                                        <label>City</label>
                                    </div>
                                    <div className="col-lg-9">
                                        <input value={boost.post_detail?.city ? boost.post_detail?.city : 'Not Available'} readOnly />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="row">
                            <div className="col-lg-12 pb-3">
                                <h3>Package Details</h3>
                            </div>
                            <div className="col-lg-10">
                                <div className="row">
                                    <div className="col-lg-3">
                                        <label>Package Type</label>
                                    </div>
                                    <div className="col-lg-9">
                                        <input value={boost?.package_type ? boost?.package_type : 'Not Available'} readOnly />
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-lg-3">
                                        <label>Package Description</label>
                                    </div>
                                    <div className="col-lg-9">
                                        {/* Render the HTML content */}
                                        {boost?.package_description === 'Not Available' ? (
                                            <input value={boost?.package_description} readOnly />
                                        ) : (
                                            <div dangerouslySetInnerHTML={{ __html: (boost?.package_description)}} />
                                        )}
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-lg-3">
                                        <label>Duration</label>
                                    </div>
                                    <div className="col-lg-9">
                                        <input value={boost?.duration ? boost?.duration : 'Not Available'} readOnly />
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-lg-3">
                                        <label>Gst in %</label>
                                    </div>
                                    <div className="col-lg-9">
                                        <input value={boost?.gst_in_percent ? boost?.gst_in_percent : 'Not Available'} readOnly />
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-lg-3">
                                        <label>Amount</label>
                                    </div>
                                    <div className="col-lg-9">
                                        <input value={boost?.amount ? boost?.amount : 'Not Available'} readOnly />
                                    </div>
                                </div>
                                <div className="row">
                                    <div className="col-lg-3">
                                        <label>Total</label>
                                    </div>
                                    <div className="col-lg-9">
                                        <input value={boost?.total ? boost?.total : 'Not Available'} readOnly />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="row">
                            <div className="col-lg-2">
                                <label>Status</label>
                            </div>
                            <div className="col-lg-9 mt-2 pl-6">
                                <select className='input-border' value={boost.status || ''} onChange={ev => {
                                    const selectedValue = ev.target.value;
                                    setBoost({ ...boost, status: selectedValue });
                                    setReason(selectedValue === 'rejected' ? boost.rejected_reason : '');
                                    setModalIsOpen(selectedValue === 'rejected');
                                }}>
                                    <option disabled value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                        {(boost.status === "rejected") && (
                            <div className="row">
                                <div className="col-lg-2">
                                    <label>Reason</label>
                                </div>
                                <div className="col-lg-9 pl-6">
                                    {errors &&
                                        <div className="alert">
                                            <p>{errors}</p>
                                        </div>
                                    }
                                    <input className='input-bdr w-8' required value={rejected_reason} onChange={handleInputChange} placeholder="Reason" disabled />
                                    <button type="button" onClick={handleEditReason} className="btn-custom btn-reason">Edit Reason</button>
                                </div>
                            </div>
                        )}
                        <Modal isOpen={modalIsOpen} onRequestClose={() => setModalIsOpen(false)} contentLabel="Reason Popup">
                            <h2 className='text-center'>Add Reason</h2>
                            <input required className='btn-block input-bdr' value={rejected_reason} onChange={handleInputChange} placeholder="Reason" />
                            <div className='text-end'>
                                <button className={`btn-custom mr-1 ${(!rejected_reason || !rejected_reason.trim()) && 'disabled'}`} onClick={handleSaveReason} disabled={!rejected_reason || !rejected_reason.trim()}>Save</button>
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
