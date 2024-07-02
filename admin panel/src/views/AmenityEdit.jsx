import React, { useEffect, useState } from 'react';
import { useParams } from "react-router-dom";
import axiosClient from "../axios-client.js";
import { useStateContext } from "../context/ContextProvider.jsx";

export default function AmenityUpdate() {
    let { amenity_id } = useParams();
    const [errors, setErrors] = useState({});
    const { setNotification } = useStateContext();
    const [amenity, setAmenity] = useState({});
    const [image, setImage] = useState(null); // State to hold the selected image file
    const [loading, setLoading] = useState(false);

    // Fetch amenity data on component mount if amenity_id exists
    useEffect(() => {
        if (amenity_id) {
            getAmenity();
        }
    }, [amenity_id]);

    // Function to fetch amenity data based on amenity_id
    const getAmenity = () => {
        setLoading(true);
        axiosClient.get(`/get-amenity/${amenity_id}`)
            .then(({ data }) => {
                setLoading(false);
                setAmenity(data.data); // Assuming your API response has a data attribute containing amenity details
            })
            .catch(err => {
                const response = err.response;
                setLoading(false);
                if (response && response.status === 422) {
                    setErrors(response.data.errors);
                }
            });
    };

    // Handle form submission to update amenity
    const handleSubmit = async (ev) => {
        ev.preventDefault();
        const formErrors = {};
        const formData = new FormData();
        formData.append('id', amenity.id);
        formData.append('amenity_name', amenity.amenity_name);

        // If a new image is selected, update formData with the new image
        if (image) {
            formData.append('icon', image);
        }

        if (!amenity.amenity_name?.trim()) {
            formErrors.amenity_name = "Amenity name is required.";
        }

        // console.log('formerror',formErrors);

        if (Object.keys(formErrors).length > 0) {
            setErrors(formErrors);
            return;
        } else {
            setErrors({});
            setLoading(true);

            try {
               
                const response = await axiosClient.post(`/addupdate-amenities`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                if (response.status === 200) {
                    setNotification('Amenity successfully updated');
                    setTimeout(() => {
                        window.location = '/amenities';
                    }, 1000);
                }
                setLoading(false);
            } catch (err) {
                const response = err.response;
                setLoading(false);
                if (response && response.status === 422 || response.status === 404) {
                    setErrors(response.data.errors);
                    if (response.status === 404) {
                        formErrors.amenity_name = response?.data?.data?.amenity_name[0];
                        setErrors(formErrors);
                    }

                }
            }
        }
    };

    // Handle file input change to set the selected image file
    const handleFileChange = (e) => {
        setImage(e.target.files[0]);
    };

    // Dummy image URL for fallback
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
                    {errors.amenity_name && <div className="alert alert-danger">{errors.amenity_name}</div>}
                    <div className="row">
                        <input type='hidden' value={amenity.id || ''} name='id' />
                        <div className="col-lg-3">
                            <label htmlFor='amenity_name'>Amenity Name</label>
                        </div>
                        <div className="col-lg-9">
                            <input
                                type='text'
                                className='package-width input-border'
                                id='amenity_name'
                                name='amenity_name'
                                value={amenity.amenity_name || ''}
                                onChange={(e) => setAmenity({ ...amenity, amenity_name: e.target.value })}
                                placeholder='Enter Amenity Name'
                            />
                        </div>
                    </div>

                    <div className="row">
                        <div className="col-lg-3">
                            <label htmlFor='icon'>Amenity Icon</label>
                        </div>
                        <div className="col-lg-9">
                            {amenity.icon ? (
                                <a href={`${import.meta.env.VITE_API_BASE_URL}/${amenity.icon}`} target="_blank" rel="noopener noreferrer">
                                    <img className='amenity-logo' src={`${import.meta.env.VITE_API_BASE_URL}/${amenity.icon}`} alt="Amenity Icon" />
                                </a>
                            ) : (
                                <img className='amenity-logo' src={dummy_image} alt="Dummy Image" />
                            )}
                        </div>
                    </div>

                    <div className="row">
                        <div className="col-lg-3">
                            {/* No label needed here */}
                        </div>
                        <div className="col-lg-9 mt-4">
                            <input
                                type='file'
                                className='package-width input-border'
                                id='icon'
                                name='icon'
                                onChange={handleFileChange}
                            />
                        </div>
                    </div>
                    <button type="submit" className="btn-custom">Update</button>
                </form>
            )}
        </div>
    );
}