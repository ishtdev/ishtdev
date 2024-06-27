import React, { useEffect, useState } from 'react';
import { useParams } from "react-router-dom";
import axiosClient from "../axios-client.js";
import { useStateContext } from "../context/ContextProvider.jsx";
import { CKEditor } from '@ckeditor/ckeditor5-react';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export default function PackageDetails() {
    let { package_id } = useParams();
    const [errors, setErrors] = useState({});
    const { setNotification } = useStateContext();
    const [user, setUser] = useState({});
    const [loading, setLoading] = useState(false);
    const [packageDescription, setPackageDescription] = useState('');

    useEffect(() => {
        getPackage();
    }, []);

    const getPackage = () => {
        setLoading(true);
        axiosClient.get(`/get-package-detail/${package_id}`)
            .then(({ data }) => {
                setLoading(false);
                setUser(data.data);
                setPackageDescription(data.data.package_description || '');
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
        const formData = new FormData(ev.target);
        const updatedUser = Object.fromEntries(formData.entries());

        if (user.id) {
            updatedUser.package_description = packageDescription;
            updatedUser.package_id = package_id;
            if (!packageDescription.trim()) {
                formErrors.package_description = "Package description is required.";
            }
            if (!updatedUser.amount?.trim()) {
                formErrors.amount = "Amount is required.";
            }
            if (!updatedUser.gst_in_percent?.trim()) {
                formErrors.gst_in_percent = "GST is required.";
            }

            if (Object.keys(formErrors).length > 0) {
                setErrors(formErrors);
                return;
            } else {
                setErrors('');
                setLoading(true);
                axiosClient.post(`/addupdate-package`, updatedUser)
                    .then(() => {
                        setNotification('Package successfully updated');
                        setTimeout(() => {
                            window.location = '/package';
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

    const profile_picture = `${import.meta.env.VITE_API_BASE_URL}/${user.profile_picture || ''}`;
    const dummy_image = `${import.meta.env.VITE_API_BASE_URL}/communitydocument/dummy-profile-pic.jpg`;

    const handleChange = (e) => {
        const { name, value } = e.target;
        setUser({ ...user, [name]: value });
    };

    return (
        <div className="card animated fadeInDown">
            {loading && (
                <div className="text-center">
                    Loading...
                </div>
            )}
            {!loading && (
                <form onSubmit={handleSubmit}>
                    {errors.package_description && <div className="alert alert-danger">{errors.package_description}</div>}
                    {errors.amount && <div className="alert alert-danger">{errors.amount}</div>}
                    {errors.gst_in_percent && <div className="alert alert-danger">{errors.gst_in_percent}</div>}
                    <input type='hidden' value={user.id || ''} name='id' />
                    <input type='hidden' value={user.profile_id || ''} name='profile_id' />

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
                            <input
                                className='package-width input-border'
                                id='package_type'
                                name='package_type'
                                value={user.package_type || ''}
                                readOnly
                            />
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
                            <select
                                className='package-width input-border'
                                id='duration'
                                name='duration'
                                value={user.duration || ''}
                                onChange={handleChange}
                                required
                            >
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
                            <input
                                type='number'
                                className='package-width input-border'
                                id='amount'
                                name='amount'
                                value={user.amount || ''}
                                onChange={handleChange}
                                placeholder='Enter amount'
                            />
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-lg-3">
                            <label>GST in %</label>
                        </div>
                        <div className="col-lg-9">
                            <input
                                type='number'
                                className='package-width input-border'
                                id='gst_in_percent'
                                name='gst_in_percent'
                                value={user.gst_in_percent || ''}
                                onChange={handleChange}
                                placeholder='Enter GST amount'
                            />
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-lg-3">
                            <label>Total</label>
                        </div>
                        <div className="col-lg-9">
                            <input
                                type='text'
                                className='package-width input-border'
                                id='total'
                                name='total'
                                value={user.total || ''}
                                readOnly
                            />
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-lg-3">
                            <label>Status</label>
                        </div>
                        <div className="col-lg-9">
                            <select
                                className='package-width input-border'
                                id='status'
                                name='status'
                                value={user.status || ''}
                                onChange={handleChange}
                                required
                            >
                                <option value="">Select status</option>
                                <option value="inactive">Inactive</option>
                                <option value="active">Active</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" className="btn-custom">Update</button>
                </form>
            )}
        </div>
    );
}
