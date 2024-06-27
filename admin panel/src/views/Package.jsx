import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axiosClient from "../axios-client.js";
import { useNavigate } from 'react-router-dom';

export default function Package() {
  const [package1, setPackage] = useState([]);
  const [loading, setLoading] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [searchResults, setSearchResults] = useState([]);
  const [jumpToPage, setJumpToPage] = useState("");
  const [errorMessage, setErrorMessage] = useState("");
  const itemsPerPage = 10;

  useEffect(() => {
    getPackage();
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
  
  const getPackage = () => {
    setLoading(true);
    axiosClient.get('/get-all-package')
      .then(({ data }) => {
        setLoading(false);
        setPackage(data.data);
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
  const navigate = useNavigate();

  const handleClick = () => {
    navigate('/create-package');
  };

  function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  }

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: "space-between", alignItems: "center" }}>
        <h1>Package</h1>
      </div>

      <div className="card animated fadeInDown">
        <div style={{ display: 'flex', justifyContent: "space-between", alignItems: "center" }} className='mb-4'>
          <button type="button" className="btn-custom" onClick={handleClick}>Create Package</button>
        </div>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Package Type</th>
              <th>Duration</th>
              <th>Amount</th>
              <th>GST</th>
              <th>Total</th>
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
              {currentItems.map(b => (
                <tr key={b.id}>
                  <td>{b.id}</td>
                  <td>{b.package_type}</td>
                  <td>{b.duration}</td>
                  <td>{b.amount}</td>
                  <td>{b.gst_in_percent}%</td>
                  <td>{b.total}</td>
                  <td style={{ color: b.status === 'active' ? 'green' : 'red' }}>
                    {capitalizeFirstLetter(b.status)}
                  </td>
                  <td>
                    <Link className="btn-custom" to={'/package-detail/' + b.id}>View</Link>
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
