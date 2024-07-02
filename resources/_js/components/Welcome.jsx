import React from 'react';
import ReactDOM from 'react-dom/client';

function Welcome() {
    return (
        <div className="container">
            <section className="vh-100">
            <div className="container py-5 h-100">
                <div className="row d-flex justify-content-center align-items-center h-100">
                    <div className="col col-xl-10">
                        <div className="card" >
                            <div className="row g-0">
                                <div className="col-md-6 col-lg-5 d-none d-md-block">
                                <img
                                    src="Image/image.png"
                                    alt="login form"
                                    className="img-fluid h-100"

                                />

                                </div>
                                <div className="col-md-6 col-lg-7 d-flex align-items-center">
                                    <div className="card-body p-4 p-lg-5 text-black">
                                        <form>
                                        <div className="d-flex align-items-center mb-3 pb-1">
                                            <i
                                            className="fas fa-cubes fa-2x me-3"

                                            ></i>
                                            <span className="h1 fw-bold mb-0 row align-items-center"
                                            ><img  src="Image/Khajrana.png" />
                                            <p className="ml-3 mb-0">
                                                Khajrana Ganesh Mandir
                                            </p></span>
                                        </div>
                                        <div className="form-outline mb-2">
                                            <input
                                            placeholder="Name"
                                            type="email"
                                            id="form2Example17"
                                            className="form-control"
                                            required
                                            />
                                        </div>

                                        <div className="form-outline mb-2">
                                            <input
                                            pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}"
                                            required
                                            placeholder="Mobile No"
                                            type="number"
                                            id="form2Example27"
                                            className="form-control"
                                            />
                                        </div>

                                        <div className="form-outline mb-2">
                                            <input
                                            required
                                            placeholder="Number of Persons"
                                            type="number"
                                            className="form-control"
                                            />
                                        </div>
                                        <div className="form-outline mb-2">
                                            <input
                                            required
                                            placeholder="Date"
                                            type="text"
                                            id="form2Example27"
                                            className="form-control"
                                            />
                                        </div>
                                        <div className="form-outline mb-2">
                                            <input
                                            required
                                            placeholder="Time"
                                            type="text"
                                            id="form2Example27"
                                            className="form-control"
                                            />
                                        </div>

                                        <div className="pt-1 mb-3">
                                            <button
                                            data-toggle="modal"
                                            data-target="#exampleModalLong"
                                            className="btn btn-dark btn-lg btn-block"
                                            type="button">
                                            Submit
                                            </button>
                                        </div>
                                        </form>
                                    </div>
                                </div>



                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </section>
        </div>
    );
}

export default Welcome;

if (document.getElementById('welcome')) {
    const Index = ReactDOM.createRoot(document.getElementById("welcome"));

    Index.render(
        <React.StrictMode>
            <Welcome/>
        </React.StrictMode>
    )
}
