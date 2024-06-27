import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axiosClient from "../axios-client.js";
import TableHeadLayout from '../componenets/TableHeadLayout.jsx';

export default function Community() {
  const [communities, setCommunities] = useState([]);
  const [loading, setLoading] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [searchQuery, setSearchQuery] = useState('');
  const [searchResults, setSearchResults] = useState([]);
  const [jumpToPage, setJumpToPage] = useState("");
  const [errorMessage, setErrorMessage] = useState(""); 
  const itemsPerPage = 10;

  useEffect(() => {
    getCommunities();
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
  
  const getCommunities = () => {
    setLoading(true);
    axiosClient.get('/showAllCommunity')
      .then(({ data }) => {
        setLoading(false);
        setCommunities(data.data);
        setSearchResults(data.data);
      })
      .catch(() => {
        setLoading(false);
      });
  };

  const handleSearch = (e) => {
    const query = e.target.value;
    setSearchQuery(query);
    const filteredCommunities = communities.filter(c => 
      c.name_of_community.toLowerCase().includes(query.toLowerCase())
    );
    setSearchResults(filteredCommunities);
    setCurrentPage(1);
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
        <h1>Communities</h1>
        <div className="search-container">
          <input
            type="text"
            placeholder="Search Community"
            value={searchQuery}
            onChange={handleSearch}
            className="search-input"
          />
          <i className="fa fa-search search-icon"></i>
        </div>
      </div>
      <div className="card animated fadeInDown">
        <table>
          <thead>
            <tr>
              <th>Sr No.</th>
              <th>Profile ID</th>
              <th>Name Of Community</th>
              <th>Created On</th>
              <th>Created At</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          {loading ? (
            <tbody>
              <tr>
                <td colSpan="7" className="text-center">
                  Loading...
                </td>
              </tr>
            </tbody>
          ) : (
            <tbody>
              {currentItems.map((c, index) => (
                <tr key={c.id}>
                  <td>{indexOfFirstItem + index + 1}</td>
                  <td>{c.profile_id}</td>
                  <td>{c.name_of_community}</td>
                  <td>{c.created_at.date}</td>
                  <td>{c.created_at.time}</td>
                  <td style={{ color: c.status === 'approved' ? 'green' : c.status === 'approved_with_tick' ? 'orange' : c.status === 'pending' ? '#ffdc09' : c.status === 'rejected' ? 'orange' : c.status === 'block' ? 'red' : 'inherit' }}>
                    {capitalizeFirstLetter(c.status)}
                  </td>
                  <td>
                    <Link className="btn-custom" to={'/community/' + c.profile_id}>View</Link>
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
