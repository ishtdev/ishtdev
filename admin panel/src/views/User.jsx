import React, { useEffect, useState } from "react";
import axiosClient from "../axios-client.js";
import { Link } from "react-router-dom";
import TableHeadLayout from "../componenets/TableHeadLayout.jsx";

export default function User() {
  const [Pandit, setPandit] = useState([]);
  const [loading, setLoading] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [jumpToPage, setJumpToPage] = useState("");
  const [errorMessage, setErrorMessage] = useState("");
  const itemsPerPage = 10;

  useEffect(() => {
    getPandit();
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
  
  const getPandit = () => {
    setLoading(true);
    axiosClient
      .get("/showAllUsers")
      .then(({ data }) => {
        setLoading(false);
        setPandit(data.data);
      })
      .catch(() => {
        setLoading(false);
      });
  };

  const indexOfLastItem = currentPage * itemsPerPage;
  const indexOfFirstItem = indexOfLastItem - itemsPerPage;
  const currentItems = Pandit.slice(indexOfFirstItem, indexOfLastItem);
  const totalPages = Math.ceil(Pandit.length / itemsPerPage);

  const paginate = (pageNumber) => setCurrentPage(pageNumber);

  function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  }

  return (
    <div>
      <div
        style={{
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
        }}
      >
        <h1>Users</h1>
      </div>
      <div className="card animated fadeInDown">
        <table>
          <TableHeadLayout name="Name" />
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
              {currentItems.map((c, index) => (
                <tr key={c.id}>
                  <td>{indexOfFirstItem + index + 1}</td>
                  <td>{c.profile_id}</td>
                  <td>{c.full_name}</td>
                  <td
                    style={{
                      color:
                        c.become_pandit === "approved"
                          ? "green"
                          : c.become_pandit === "pending"
                            ? "#ffdc09"
                            : c.become_pandit === "rejected"
                              ? "orange"
                              : c.become_pandit === "block"
                                ? "red"
                                : "inherit",
                    }}
                  >
                    {capitalizeFirstLetter(c.become_pandit)}
                  </td>
                  <td>
                    <Link className="btn-custom" to={"/user/" + c.profile_id}>
                      View
                    </Link>
                    &nbsp;
                  </td>
                </tr>
              ))}
            </tbody>
          )}
        </table>

        <div className="pagination">
          <button
            className={`btn-pagination paginationButton ${currentPage === 1 ? "disabled" : ""
              }`}
            onClick={() => paginate(currentPage - 1)}
            disabled={currentPage === 1}
          >
            {"<"}
          </button>
          <span className="currentPageNumber">
            Page{" "}
            <span style={{ color: "#eb6238", fontWeight: "bold" }}>
              {currentPage}
            </span>{" "}
            of {totalPages}
          </span>
          <button
            className={`btn-pagination paginationButton ${currentPage === totalPages ? "disabled" : ""
              }`}
            onClick={() => paginate(currentPage + 1)}
            disabled={currentPage === totalPages}
          >
            {">"}
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
