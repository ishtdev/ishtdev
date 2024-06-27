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
        const updatedUser = Object.fromEntries(formData.entries());


        const amenityName = formData.get('amenity_name');
        const icon = formData.get('icon');

        if (!amenityName?.trim()) {
            formErrors.amenity_name = "Amenity Name is required.";
        }
        if (!icon || icon.size === 0) {
            formErrors.icon = "Amenity Icon is required.";
        }
        // console.log('form', updatedUser);

        if (Object.keys(formErrors).length > 0) {
            setErrors(formErrors);
            return;
        } else {
            setErrors({});
            setLoading(true);

            try {
                const response = await axiosClient.post('/addupdate-amenities', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                if (response.status === 200) {
                    
                        setNotification('Amenity created successfully');
                        // Redirect after 1 second
                        setTimeout(() => {
                            window.location = '/amenities';
                        }, 1000);
                   
                }

                setLoading(false);
            } catch (err) {
                const response = err.response;
                // console.log('error', response);
                setLoading(false);
                if (response && (response.status === 422 || response.status === 404)) {
                    setErrors({ errors: response?.data?.message });
                    if (response && response.status === 422 || response.status === 404) {
                        setErrors(response.data.errors);
                        if (response.status === 404) {
                            formErrors.amenity_name = response?.data?.data?.amenity_name[0];
                            setErrors(formErrors);
                        }
    
                    }
                }
                else {
                    setErrors({ errors: 'An unexpected error occurred.' });
                }
            }
        }
    };

    return (
        <div className="card animated fadeInDown">
            <h3 className='pb-3'>Create Amenity</h3>
            {loading && (
                <div className="text-center">
                    Loading...
                </div>
            )}
            {!loading && (
                <form onSubmit={handleSubmit}>
                    {errors.errors && <div className="alert alert-danger">{errors.errors}</div>}
                    {errors.amenity_name && <div className="alert alert-danger">{errors.amenity_name}</div>}
                    {errors.icon && <div className="alert alert-danger">{errors.icon}</div>}

                    <div className="row">
                        <div className="col-lg-3">
                            <label>Amenity Name</label>
                        </div>
                        <div className="col-lg-9">
                            <input type='text' className='package-width input-border' id='amenity_name' name='amenity_name' placeholder='Enter Amenity Name' />
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-lg-3">
                            <label>Amenity Icon</label>
                        </div>
                        <div className="col-lg-9">
                            <input type='file' className='package-width input-border' id='icon' name='icon' />
                        </div>
                    </div>
                    <button type="submit" className="btn-custom">Create</button>
                </form>
            )}
        </div>
    );
}