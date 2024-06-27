import React, { useEffect, useState } from 'react';
import { useParams } from "react-router-dom";
import axiosClient from "../axios-client.js";
import { useStateContext } from "../context/ContextProvider.jsx";

export default function ReportedPostDetails() {
  let { post_id } = useParams();
  const [reportPost, setReportPost] = useState(null);
  const [loading, setLoading] = useState(false);
  const { setNotification } = useStateContext();

  useEffect(() => {
    getReportPost();
  }, []);

  const restoreReportedPost = () => {
    if (window.confirm('Are you sure you want to restore this post?')) {
      setLoading(true);
      axiosClient.get(`/restore-reported-post/${post_id}`)
        .then(({ data }) => {
          this.timer = setTimeout(() => {
            window.location = '/reported-post';
          }, 1000);
        })
        .catch(() => {
          setLoading(false);
        });
    }
  };

  const deletePost = () => {
    if (window.confirm('Are you sure you want to delete this post?')) {
      setLoading(true);
      axiosClient.delete(`/delete-post-by-admin/${post_id}`)
        .then(({ data }) => {
          window.location.reload();
          setLoading(true);
        })
        .catch(() => {
          setLoading(false);
        });
    }
  };

  const getReportPost = () => {
    axiosClient.get(`/get-reported-post/${post_id}`)
      .then(({ data }) => {

        // console.log('data', data.data.post);
        setReportPost(data.data.post);
      })
      .catch(err => {
        const response = err.response;
        if (response && response.status === 422) {
          setNotification('Post not found.');
        }
      });
  };

  // const post_image = reportPost && reportPost.post_related_data && !reportPost.post_related_data[0].post_data.endsWith('.mp4') && !reportPost.post_related_data[0].post_data.endsWith('.gif')
  //   ? `${import.meta.env.VITE_API_BASE_URL}/${reportPost?.post_related_data[0]?.post_data}`
  //   : '';

  const dummy_image = `${import.meta.env.VITE_API_BASE_URL}/communitydocument/dummy-profile-pic.jpg`;

  return (
    <div className="card animated fadeInDown">
      {reportPost && (
        <div>
          <div className="row pb-3">
            <div className="col-lg-12 pb-3">
              <h3>Post Details</h3>
            </div>
            <div className="col-lg-3">
              {reportPost?.post_related_data[0]?.post_data ? (
                <a href={`${import.meta.env.VITE_API_BASE_URL}/${reportPost.post_related_data[0].post_data}`} target="_blank" rel="noopener noreferrer">
                  <img className='profile_picture' src={`${import.meta.env.VITE_API_BASE_URL}/${reportPost.post_related_data[0].post_data}`} alt="Post Image" />
                </a>
              ) : (
                <img className='profile_picture' src={dummy_image} alt="Dummy Image" />
              )}
            </div>
            <div className="col-lg-9">
              <div className="row">
                <div className="col-lg-3">
                  <label>Post Deleted</label>
                </div>
                <div className="col-lg-9">
                  <input value={reportPost?.deleted_at ? 'Yes' : 'No'} readOnly />
                </div>
              </div>
              <div className="row">
                <div className="col-lg-3">
                  <label>Report Count</label>
                </div>
                <div className="col-lg-9">
                  <input value={reportPost?.reportCount} readOnly />
                </div>
              </div>
              <div className="row">
                <div className="col-lg-3">
                  <label>Posted By</label>
                </div>
                <div className="col-lg-9">
                  <input value={reportPost?.profile?.user.username ? reportPost?.profile?.user.username : 'Not Available'} readOnly />
                </div>
              </div>
              <div className="row">
                <div className="col-lg-3">
                  <label>Caption</label>
                </div>
                <div className="col-lg-9">
                  <input value={reportPost?.caption ? reportPost?.caption : 'Not Available'} readOnly />
                </div>
              </div>
              <div className="row">
                <div className="col-lg-3">
                  <label>Like Count</label>
                </div>
                <div className="col-lg-9">
                  <input value={reportPost?.likeCount} readOnly />
                </div>
              </div>
              <div className="row">
                <div className="col-lg-3">
                  <label>Comment Count</label>
                </div>
                <div className="col-lg-9">
                  <input value={reportPost?.commentCount} readOnly />
                </div>
              </div>
              <div className="row">
                <div className="col-lg-3">
                  <label>Post Type</label>
                </div>
                <div className="col-lg-9">
                  <input value={reportPost?.post_type === '1' ? 'Image' : 'Video'} readOnly />
                </div>
              </div>
              <div className="row">
                <div className="col-lg-3">
                  <label>City</label>
                </div>
                <div className="col-lg-9">
                  <input value={reportPost?.city ? reportPost?.city : 'Not Available'} readOnly />
                </div>
              </div>
              <div className="row">
                <div className="col-lg-3">
                  <label>Interest</label>
                </div>
                <div className="col-lg-9">
                  <input value={reportPost?.name_of_interest ? reportPost?.name_of_interest : 'Not Available'} readOnly />
                </div>
              </div>
              <div className="row">
                <div className="col-lg-3">
                  <label>Boost Status</label>
                </div>
                <div className="col-lg-9">
                  <input value={reportPost?.boost_status ? reportPost?.boost_status : 'Not Available'} readOnly />
                </div>
              </div>
              <div className="row">
                <div className="col-lg-3">
                  <label>Boost Expiring On</label>
                </div>
                <div className="col-lg-9">
                  <input value={reportPost?.Expiry_on ? reportPost?.Expiry_on : 'Not Available'} readOnly />
                </div>
              </div>
              <a
                href="#"
                className="btn-custom"
                onClick={(e) => {
                  e.preventDefault();
                  restoreReportedPost();
                }}
              >
                Restore Post
              </a>
              {reportPost?.deleted_at === null && (
                <a
                  href="#"
                  className="btn-custom ml-2"
                  onClick={(e) => {
                    e.preventDefault();
                    deletePost();
                  }}
                >
                  Delete Post
                </a>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
