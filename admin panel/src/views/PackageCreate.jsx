import React, { useState } from 'react';
import axiosClient from "../axios-client.js";
import { useStateContext } from "../context/ContextProvider.jsx";
import { CKEditor } from '@ckeditor/ckeditor5-react';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export default function PackageCreate() {
    const [errors, setErrors] = useState({});
    const { setNotification } = useStateContext();
    const [user, setUser] = useState({});
    const [loading, setLoading] = useState(false);
    const [packageDescription, setPackageDescription] = useState('');

    let profile_id = localStorage.getItem('profile_id');

    const handleSubmit = (ev) => {
        ev.preventDefault();

        const formErrors = {};
        const formData = new FormData(ev.target);
        const updatedUser = Object.fromEntries(formData.entries());

        if (profile_id) {
            updatedUser.package_description = packageDescription;

            if (!updatedUser.package_type?.trim()) {
                formErrors.package_type = "Package type is required.";
            }
            if (!updatedUser.package_description?.trim()) {
                formErrors.package_description = "Package description is required.";
            }
            if (!updatedUser.duration?.trim()) {
                formErrors.duration = "Duration is required.";
            }
            if (!updatedUser.amount?.trim()) {
                formErrors.amount = "Amount is required.";
            }
            if (!updatedUser.gst_in_percent?.trim()) {
                formErrors.gst_in_percent = "GST is required.";
            }
            if (!updatedUser.status?.trim()) {
                formErrors.status = "Status is required.";
            }

            if (Object.keys(formErrors).length > 0) {
                setErrors(formErrors);
                return;
            } else {
                setErrors({});
                setLoading(true);

                axiosClient.post(`/addupdate-package`, updatedUser)
                    .then(() => {
                        setNotification('Package successfully created');
                        setTimeout(() => {
                            window.location = '/package';
                        }, 1000);
                    })
                    .catch(err => {
                        const response = err.response;
                        setLoading(false);
                        if (response && response.status === 422 || response.status === 404) {
                            formErrors.errors = response?.data?.message;
                            setErrors(formErrors);
                        }
                    });
            }
        }
    };

    const profile_picture = `${import.meta.env.VITE_API_BASE_URL}/${user.profile_picture || ''}`;
    const dummy_image = `${import.meta.env.VITE_API_BASE_URL}/communitydocument/dummy-profile-pic.jpg`;

    return (
        <div className="card animated fadeInDown">
            {loading && (
                <div className="text-center">
                    Loading...
                </div>
            )}
            {!loading && (
                <form onSubmit={handleSubmit}>
                     {errors.errors && <div className="alert alert-danger">{errors.errors}</div>}
                    {errors.package_type && <div className="alert alert-danger">{errors.package_type}</div>}
                    {errors.package_description && <div className="alert alert-danger">{errors.package_description}</div>}
                    {errors.duration && <div className="alert alert-danger">{errors.duration}</div>}
                    {errors.amount && <div className="alert alert-danger">{errors.amount}</div>}
                    {errors.gst_in_percent && <div className="alert alert-danger">{errors.gst_in_percent}</div>}
                    {errors.status && <div className="alert alert-danger">{errors.status}</div>}

                    <input type='number' hidden value={profile_id} id='profile_id' name='profile_id' />
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
                            <label>Package Type</label>
                        </div>
                        <div className="col-lg-9">
                            <select className='package-width input-border' id='package_type' name='package_type'>
                                <option value="">Select package</option>
                                <option value="Bronze">Bronze</option>
                                <option value="Silver">Silver</option>
                                <option value="Gold">Gold</option>
                            </select>
                        </div>
                    </div>
                    <div className="row mb-2">
                        <div className="col-lg-3">
                            <label>Description</label>
                        </div>
                        <div className="col-lg-9">
                            <CKEditor
                                editor={ClassicEditor}
                                data={packageDescription}
                                onChange={(event, editor) => {
                                    const data = editor.getData();
                                    setPackageDescription(data);
                                }}
                            />
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-lg-3">
                            <label>Duration</label>
                        </div>
                        <div className="col-lg-9">
                            <select className='package-width input-border' id='duration' name='duration'>
                                <option value="">Select duration</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-lg-3">
                            <label>Amount</label>
                        </div>
                        <div className="col-lg-9">
                            <input type='number' className='package-width input-border' id='amount' name='amount' placeholder='Enter amount' />
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-lg-3">
                            <label>GST in %</label>
                        </div>
                        <div className="col-lg-9">
                            <input type='number' className='package-width input-border' id='gst_in_percent' name='gst_in_percent' placeholder='Enter GST amount' />
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-lg-3">
                            <label>Status</label>
                        </div>
                        <div className="col-lg-9">
                            <select className='package-width input-border' value={user.status || ''} onChange={ev => {
                                const selectedValue = ev.target.value;
                                setUser({ ...user, status: selectedValue });
                            }} id='status' name='status'>
                                <option value="">Select status</option>
                                <option value="inactive">Inactive</option>
                                <option value="active">Active</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" className="btn-custom">Save</button>
                </form>
            )}
        </div>
    );
}
