import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axiosClient from "../axios-client.js";
import { useNavigate } from 'react-router-dom';

export default function Badge() {
  // const [lordName, setLordName] = useState([]);
  const [loading, setLoading] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [searchResults, setSearchResults] = useState([]);
  const [jumpToPage, setJumpToPage] = useState("");
  const [errorMessage, setErrorMessage] = useState("");
  const itemsPerPage = 10;


  useEffect(() => {
    getLordBadge();
  }, []);



  const handleKeyDown = (e) => {
    if (e.key === '-' || e.key === 'e') {
      e.preventDefault();
    }
  };
  
  const handleJumpToPage = () => {
    const page = parseInt(jumpToPage, 10);
    if (page >= 1 && page <= totalPages) {
      setCurrentPage(page);
      setErrorMessage(""); // Clear any previous error messages
    } else {
      setErrorMessage(`Page number ${page} is out of range. Please enter a number between 1 and ${totalPages}.`);
    }
  };

  const getLordBadge = () => {
    setLoading(true);
    axiosClient.get('/getLordBadge')
      .then(({ data }) => {
        setLoading(false);
        // lordName(data.data);

        // console.log('data', data.data);
        
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

  const navigate = useNavigate();

  const handleClick = () => {
    navigate('/create-badge');
  };

  const paginate = pageNumber => {
    setCurrentPage(pageNumber);
  };

  function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  }

  return (
    <div>
      <div style={{ display: 'flex', justifyContent: "space-between", alignItems: "center" }}>
        <h1>Badges</h1>
      </div>
      <div className="card animated fadeInDown">
        <div style={{ display: 'flex', justifyContent: "space-between", alignItems: "center" }} className='mb-4'>
          <button type="button" className="btn-custom" onClick={handleClick}>Create Badge</button>
        </div>
        <table>
          <thead>
            <tr>
              <th>Sr No.</th>
              <th>Lord</th>
              <th>Badge Type</th>
              <th>Image</th>
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
              {currentItems.map((b, index) => (
                <tr key={b.id}>
                  <td>{indexOfFirstItem + index + 1}</td>
                  <td>{b?.lord?.lord_name }</td>
                  <td>
                   {b?.type }
                  </td>
                  <td>
                  <a href={`${import.meta.env.VITE_API_BASE_URL}/${b.image}`} target="_blank" rel="noopener noreferrer">
                                    <img   style={{ width: '50px', height: '50px' }}  src={`${import.meta.env.VITE_API_BASE_URL}/${b.image}`} alt="Amenity Icon" />
                                </a>
                  </td>
                  <td>
                    <Link className="btn-custom mr-3" to={'/badge-edit/' + b.id}>Edit</Link>
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
