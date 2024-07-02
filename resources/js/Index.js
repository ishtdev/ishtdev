import React from 'react';
import ReactDOM from 'react-dom';
import Header from "./components/ExampleComponent";



function Index() {
    return (
        <div className="Index">
            <Header/>
        </div>
    );
}

export default Index;

if (document.getElementById('index')) {
    ReactDOM.render(<Provider store={store}><Index/></Provider>, document.getElementById('index'));
}