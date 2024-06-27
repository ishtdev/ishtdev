import { Navigate, createBrowserRouter } from "react-router-dom";
import Community from "./views/Community.jsx";
import Business from "./views/Business.jsx";
import BusinessDetails from "./views/BusinessDetails.jsx";
import User from "./views/User.jsx";
import NotFound from "./views/NotFound.jsx";
import Dashboard from "./views/Dashboard.jsx";
import DefaultLayout from "./componenets/DefaultLayout.jsx";
import GuestLayout from "./componenets/GuestLayout.jsx";
import UserDetails from "./views/UserDetails.jsx";
import CommunityDetails from "./views/CommunityDetails.jsx";
import VerifyAdmin from "./views/VerifyAdmin.jsx";
import VerifyOtp from "./views/VerifyOtp.jsx";
import CommunityShare from "./views/CommunityShare.jsx";
import PostShare from "./views/PostShare.jsx";
import Wishlist from "./views/Wishlist.jsx";
import WishlistDetails from "./views/WishlistDetails.jsx";
import GreenTick from "./views/GreenTick.jsx";
import GreenTickDetails from "./views/GreenTickDetails.jsx";
import PackageCreate from "./views/PackageCreate.jsx";
import Package from "./views/Package.jsx";
import PackageDetails from "./views/PackageDetails.jsx";
import BoostRequest from "./views/BoostRequest.jsx";
import BoostRequestDetails from "./views/BoostRequestDetails.jsx";
import ReportedPost from "./views/ReportedPost.jsx";
import ReportedPostDetails from "./views/ReportedPostDetails.jsx";
import Amenities from "./views/Amenities.jsx";
import AmenityCreate from "./views/AmenityCreate.jsx";
import AmenityEdit from "./views/AmenityEdit.jsx";

const router = createBrowserRouter([
    {
        path: "/",
        element: <GuestLayout />,
        children: [
            {
                path: "/",
                element: <Navigate to={'/verifyAdmin'} />
            },
            {
                path: "/verifyAdmin",
                element: <VerifyAdmin />
            },
            {
                path: "/verifyAdmin/otp",
                element: <VerifyOtp />
            },
            {
                path: '/community-share/:profile_id',
                element: <CommunityShare />
            },
            {
                path: '/post-share/:post_id',
                element: <PostShare />
            },]
    },
    {
        path: "/",
        element: <DefaultLayout />,
        children: [
            {
                path: "/dashboard",
                element: <Dashboard />
            },
            {
                path: "/user",
                element: <User />
            },
            {
                path: '/user/:profile_id',
                element: <UserDetails />
            },
            {
                path: "/community",
                element: <Community />
            },
            {
                path: '/community/:profile_id',
                element: <CommunityDetails />
            },
            {
                path: '/wishlist',
                element: <Wishlist />
            },
            {
                path: '/wishlist/:wishlist_id',
                element: <WishlistDetails />
            },
            {
                path: '/business',
                element: <Business />
            },
            {
                path: '/business/:profile_id',
                element: <BusinessDetails />
            },
            {
                path: '/green-tick',
                element: <GreenTick />
            },
            {
                path: '/green-tick/:profile_id',
                element: <GreenTickDetails />
            },
            
            {
                path: '/create-package',
                element: <PackageCreate />
            },
            {
                path: '/package',
                element: <Package />
            },
            {
                path: '/package-detail/:package_id',
                element: <PackageDetails />
            },
            {
                path: '/boost-request',
                element: <BoostRequest />
            },
            {
                path: '/boost-request/:boost_id',
                element: <BoostRequestDetails />
            },
            {
                path: '/reported-post',
                element: <ReportedPost />
            },
            {
                path: '/reported-post/:post_id',
                element: <ReportedPostDetails />
            },
            {
                path: '/amenities',
                element: <Amenities />
            },
            {
                path: '/create-amenity',
                element: <AmenityCreate />
            },
            {
                path: '/edit-amenity/:amenity_id',
                element: <AmenityEdit />
            },
        ]
    },
    {
        path: "*",
        element: <NotFound />
    }
])
export default router;




