import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axiosClient from "../axios-client.js";
import TableHeadLayout from '../componenets/TableHeadLayout.jsx';

export default function Business() {
  const [business, setBusiness] = useState([]);
  const [loading, setLoading] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [searchQuery, setSearchQuery] = useState('');
  const [searchResults, setSearchResults] = useState([]);
  const [jumpToPage, setJumpToPage] = useState("");
  const [errorMessage, setErrorMessage] = useState("");
  const itemsPerPage = 10;

  useEffect(() => {
    getBusiness();
  }, []);

  const handleJumpToPage = () => {
    const page = parseInt(jumpToPage, 10);
    if (page >= 1 && page <= totalPages) {
      setCurrentPage(page);
      setErrorMessage(""); // Clear any previous error messages
    } else {
      setErrorMessage(`Page number ${page} is out of range. Please enter a number between 1 and ${totalPages}.`);
    }
  };

  const handleKeyDown = (e) => {
    if (e.key === '-' || e.key === 'e') {
      e.preventDefault();
    }
  };

  const getBusiness = () => {
    setLoading(true);
    axiosClient.get('/showAllBusiness')
      .then(({ data }) => {
        setLoading(false);
        setBusiness(data.data);
        setSearchResults(data.data);
      })
      .catch(() => {
        setLoading(false);
      });
  };

  const indexOfLastItem = currentPage * itemsPerPage;
  const indexOfFirstItem = indexOfLastItem - itemsPerPage;
  const currentItems = searchResults.slice(indexOfFirstItem, indexOfLastItem);
  const totalPages = Math.ceil(searchResults.length / itemsPerPage);

  const paginate = pageNumber => setCurrentPage(pageNumber);

  function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  }

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: "space-between", alignItems: "center" }}>
        <h1>Business</h1>
      </div>
      <div className="card animated fadeInDown">
        <table>
          <thead>
            <tr>
              <th>Sr No.</th>
              <th>Name</th>
              <th>Business Type</th>
              <th>Business Name</th>
              <th>GST Number</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          {loading ? (
            <tbody>
              <tr>
                <td colSpan="5" className="text-center">
                  Loading...
                </td>
              </tr>
            </tbody>
          ) : (
            <tbody>

              {currentItems.map((b, index) => (
                <tr key={b.id}>
                  <td>{indexOfFirstItem + index + 1}</td>
                  <td>{b.full_name}</td>
                  <td>{b.business_type}</td>
                  <td>{b.business_name}</td>
                  <td>{b.gst_number}</td>
                  <td style={{ color: b.business_verification_status === 'approved' ? 'green' : b.business_verification_status === 'approved_with_tick' ? 'orange' : b.business_verification_status === 'pending' ? '#ffdc09' : b.business_verification_status === 'rejected' ? 'orange' : b.business_verification_status === 'block' ? 'red' : 'inherit' }}>
                    {capitalizeFirstLetter(b.business_verification_status)}
                  </td>

                  <td>
                    <Link className="btn-custom" to={'/business/' + b.profile_id}>View</Link>
                  </td>
                </tr>
              ))}
            </tbody>
          )}
        </table>
        <div className="pagination">
          <button
            className={`btn-pagination paginationButton ${currentPage === 1 ? 'disabled' : ''}`}
            onClick={() => paginate(currentPage - 1)}
            disabled={currentPage === 1}
          >
            {'<'}
          </button>
          <span className="currentPageNumber">
            Page <span style={{ color: '#eb6238', fontWeight: 'bold' }}>{currentPage}</span> of {totalPages}
          </span>
          <button
            className={`btn-pagination paginationButton ${currentPage === totalPages ? 'disabled' : ''}`}
            onClick={() => paginate(currentPage + 1)}
            disabled={currentPage === totalPages}
          >
            {'>'}
          </button>
        </div>
        <div className="jump-to-page" >
          <input
            type="number"
            value={jumpToPage}
            onChange={(e) => setJumpToPage(e.target.value)}
            className="form-control"
            min="0"
            onKeyDown={handleKeyDown}
          />
          <button className='btn-custom' onClick={handleJumpToPage}>Jump to Page</button>
          {errorMessage && (
            <div className="pagination-error">
              {errorMessage}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
