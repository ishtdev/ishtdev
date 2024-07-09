import React, { useEffect, useState } from 'react';
import { useParams } from "react-router-dom";
import axiosClient from "../axios-client.js";

export default function PostShare() {
    let { post_id } = useParams();
    const [errors, setErrors] = useState(null);
    const [Post, setPost] = useState({});
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        getPost();
    }, []);

    const getPost = () => {
        setLoading(true);
        axiosClient.get(`/posts/${post_id}`)
            .then(({ data }) => {
                setLoading(false);
                setPost(data.data.post);

            })
            .catch(err => {
                const response = err.response;
                setLoading(false);
                if (response && response.status === 422) {
                    setErrors(response.data.errors);
                }
            });
    };

    const post_image = Post && Post.post_related_data && !Post.post_related_data[0].post_data.endsWith('.mp4') && !Post.post_related_data[0].post_data.endsWith('.gif')
        ? `${import.meta.env.VITE_API_BASE_URL}/${Post.post_related_data[0].post_data}`
        : '';
    const dummy_image = `${import.meta.env.VITE_API_BASE_URL}/communitydocument/dummy-profile-pic.jpg`;
    const background_image = `${import.meta.env.VITE_API_BASE_URL}/postImage/background-image.jpg`;
    const logo = `${import.meta.env.VITE_API_BASE_URL}/image/logo.png`;

    return (
        <section class="bg-image">
            <div className='container top-12'>
                <div className='text-center p-3'>
                    <img src={logo} alt="logo" className='width-50 w-1 mb-4' />
                </div>
                {Post && (
                    <div class="text-center">
                        {post_image ? (
                            <a href={post_image} target="_blank" rel="noopener noreferrer">
                                <img className='post-image' src={post_image} />
                            </a>
                        ) : (
                            <img className='post-image' src={dummy_image} />
                        )}
                        <h4 className='mt-2'>{Post.caption}</h4>
                        <p className='display-block text-color text-dark mb-0'>{Post.likeCount ? `${Post.likeCount} Likes` : ''}</p>
                        <p className='display-block text-color text-dark'>{Post.commentCount ? `${Post.commentCount} Comments` : ''}</p>
                        <a href='https://play.google.com/store/apps/details?id=com.in.ishtdev' rel="noopener noreferrer" target='_blank'><button class="btn-custom">Downlaod the App Now</button></a>
                    </div>
                )}
                {errors && <p>Error: {errors}</p>}
            </div>
        </section>
    );
}
