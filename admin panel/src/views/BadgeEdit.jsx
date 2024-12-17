import React, { useEffect, useState } from 'react';
import { useParams } from "react-router-dom";
import axiosClient from "../axios-client.js";
import { useStateContext } from "../context/ContextProvider.jsx";

export default function LordBadgeUpdate() {
    let { id } = useParams();
    const [errors, setErrors] = useState({});
    const { setNotification } = useStateContext();
    const [lordBadge, setLordBadge] = useState({});
    const [image, setImage] = useState(null); // State to hold the selected image file
    const [loading, setLoading] = useState(false);

    // Fetch lordBadge data on component mount if id exists
    useEffect(() => {
        if (id) {
            getLordBadgeDetail(id);
        }
    }, [id]);

    // Function to fetch lordBadge data based on id
    const getLordBadgeDetail = (id) => {
        setLoading(true);
        axiosClient.get(`/getLordBadgeDetail/${id}`)
            .then(({ data }) => {
                setLoading(false);
                setLordBadge(data.data);
            })
            .catch(err => {
                const response = err.response;
                setLoading(false);
                if (response && response.status === 422) {
                    setErrors(response.data.errors);
                }
            });
    };

    // Handle form submission to update lordBadge
    const handleSubmit = async (ev) => {
        ev.preventDefault();
        const formErrors = {};
        const formData = new FormData();
       
       
        formData.append('id', lordBadge.id);
        // formData.append('lord_name', lordBadge?.lord?.lord_name || '');
        // If a new image is selected, update formData with the new image
        if (image) {
            formData.append('image', image);
        }
        // const updatedUser = Object.fromEntries(formData.entries());
        // console.log('updated form', updatedUser);
        
        // if (!lordBadge?.lord?.lord_name?.trim()) {  // This ensures we are checking the correct field
        //     formErrors.lord_name = "Lord name is required.";
        // }
        if (Object.keys(formErrors).length > 0) {
            setErrors(formErrors);
            return;
        } else {
            setErrors({});
            setLoading(true);

            try {
                const response = await axiosClient.post(`/update-lord-badge`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                if (response.status === 200) {
                    setNotification('Badge successfully updated');
                    setTimeout(() => {
                        window.location = '/badge';
                    }, 1000);
                }
                setLoading(false);
            } catch (err) {
                const response = err.response;
                setLoading(false);
                if (response && (response.status === 422 || response.status === 404)) {
                    setErrors(response.data.errors);
                    if (response.status === 404) {
                        // formErrors.lord_name = response?.data?.data?.lord_name[0];
                        // setErrors(formErrors);
                    }
                }
            }
        }
    };

    // Handle file input change to set the selected image file
    const handleFileChange = (e) => {
        setImage(e.target.files[0]);
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
                    {errors.lord_name && <div className="alert alert-danger">{errors.lord_name}</div>}
                    <div className="row">
                        <input type='hidden' value={lordBadge.id || ''} name='id' />
                        <div className="col-lg-3">
                            <label htmlFor='lord_name'>Lord Name</label>
                        </div>
                        <div className="col-lg-9">
                            <input
                                type='text'
                                className='package-width input-border'
                                id='lord_name'
                                name='lord_name'
                                value={lordBadge?.lord?.lord_name || ''}
                                // onChange={(e) => setLordBadge({ ...lordBadge, lord: { ...lordBadge.lord, lord_name: e.target.value } })}
                                placeholder='Enter Lord Name'
                            disabled/>
                        </div>
                    </div>

                    <div className="row">
                        <input type='hidden' value={lordBadge.id || ''} name='id' />
                        <div className="col-lg-3">
                            <label htmlFor='lord_name'>Badge Type</label>
                        </div>
                        <div className="col-lg-9">
                            <input
                                type='text'
                                className='package-width input-border'
                                value={lordBadge?.type|| ''}
                            disabled/>
                        </div>
                    </div>

                    <div className="row">
                        <div className="col-lg-3">
                            <label htmlFor='image'>Badge Image</label>
                        </div>
                        <div className="col-lg-9">
                            {lordBadge.image && (
                                <a href={`${import.meta.env.VITE_API_BASE_URL}/${lordBadge.image}`} target="_blank" rel="noopener noreferrer">
                                    <img className='amenity-logo' src={`${import.meta.env.VITE_API_BASE_URL}/${lordBadge.image}`} alt="Badge Image" />
                                </a>
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
                                id='badge_image'
                                name='badge_image'
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
