import React, { useState } from 'react';
import axiosClient from "../axios-client.js";
import { useStateContext } from "../context/ContextProvider.jsx";

export default function AmenityCreate() {
    const [errors, setErrors] = useState({});
    const { setNotification } = useStateContext();
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (ev) => {
        ev.preventDefault();

        const formErrors = {};
        const formData = new FormData(ev.target);
        // const updatedUser = Object.fromEntries(formData.entries());

        formData.append('silver_type', 'Silver');
        formData.append('gold_type', 'Gold');
        formData.append('bronze_type', 'Bronze');

        const lordName = formData.get('lord_name');
        const silver_image = formData.get('silver_image');
        const gold_image = formData.get('gold_image');
        const bronze_image = formData.get('bronze_image');
              
        if (!lordName?.trim()) {
            formErrors.lord_name = "Lord Name is required.";
        }
        if (!silver_image || silver_image.size === 0) {
            formErrors.silver_image = "Silver Bagde image is required.";
        }
        if (!gold_image || gold_image.size === 0) {
            formErrors.gold_image = "Gold Bagde image is required.";
        }
        if (!bronze_image || bronze_image.size === 0) {
            formErrors.bronze_image = "Bronze Bagde image is required.";
        }
       

        if (Object.keys(formErrors).length > 0) {
            setErrors(formErrors);
            return;
        } else {
            setErrors({});
            setLoading(true);

            try {
                const response = await axiosClient.post('/add-lord-badge', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                if (response.status === 200) {
                    
                        setNotification('Badge created successfully');
                        // Redirect after 1 second
                        setTimeout(() => {
                            window.location = '/badge';
                        }, 1000);
                }

                setLoading(false);
            } catch (err) {
                const response = err.response;
                setLoading(false);
                if (response && (response.status === 422 || response.status === 404)) {
                    setErrors({ errors: response?.data?.message });
                    if (response && response.status === 422 || response.status === 404) {
                        setErrors(response.data.errors);
                        if (response.status === 404) {
                            formErrors.lord_name = response?.data?.data?.lord_name[0];
                            setErrors(formErrors);
                        }
                    }
                }
                else {
                    // console.log('errors:', response);
                    
                    setErrors({ errors: 'An unexpected error occurred.' });
                }
            }
        }
    };

    return (
        <div className="card animated fadeInDown">
            <h3 className='pb-3'>Create Badge</h3>
            {loading && (
                <div className="text-center">
                    Loading...
                </div>
            )}
            {!loading && (
                <form onSubmit={handleSubmit}>
                    {errors.errors && <div className="alert alert-danger">{errors.errors}</div>}
                    {errors.lord_name && <div className="alert alert-danger">{errors.lord_name}</div>}
                    {errors.silver_image && <div className="alert alert-danger">{errors.silver_image}</div>}
                    {errors.gold_image && <div className="alert alert-danger">{errors.gold_image}</div>}
                    {errors.bronze_image && <div className="alert alert-danger">{errors.bronze_image}</div>}

                    <div className="row">
                        <div className="col-lg-3">
                            <label>Lord Name</label>
                        </div>
                        <div className="col-lg-9">
                            <input type='text' className='package-width input-border' id='lord_name' name='lord_name' placeholder='Enter Lord Name' />
                        </div>
                    </div>

                    {/* Silver */}

                    <div className="row">
                        <div className="col-lg-3">
                            <label>Badge Type</label>
                        </div>
                        <div className="col-lg-9">
                            <input type='text' id="silver_type" name="silver_type" value={"Silver"} className='package-width input-border' disabled/>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-lg-3">
                            <label>Image</label>
                        </div>
                        <div className="col-lg-9">
                            <input type='file' id="silver_image" name="silver_image" className='package-width input-border'/>
                        </div>
                    </div>

                    {/* Gold */}

                    <div className="row">
                        <div className="col-lg-3">
                            <label>Badge Type</label>
                        </div>
                        <div className="col-lg-9">
                            <input type='text' id="gold_type" name="gold_type" value={"Gold"} className='package-width input-border' disabled/>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-lg-3">
                            <label>Image</label>
                        </div>
                        <div className="col-lg-9">
                            <input type='file' id="gold_image" name="gold_image" className='package-width input-border'/>
                        </div>
                    </div>

                    {/* Bronze */}

                    <div className="row">
                        <div className="col-lg-3">
                            <label>Badge Type</label>
                        </div>
                        <div className="col-lg-9">
                            <input type='text' id="bronze_type" name="bronze_type" value={"Bronze"} className='package-width input-border' disabled/>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-lg-3">
                            <label>Image</label>
                        </div>
                        <div className="col-lg-9">
                            <input type='file' id="bronze_image" name="bronze_image" className='package-width input-border'/>
                        </div>
                    </div>

                    <button type="submit" className="btn-custom">Create</button>
                </form>
            )}
        </div>
    );
}