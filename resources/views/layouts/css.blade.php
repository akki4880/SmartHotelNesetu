<meta name="description" content="Alerts">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no, minimal-ui">
<meta name="apple-mobile-web-app-title" content="Alerts">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<!-- Mobile proof your site -->
<link rel="manifest" href="{{asset('assets/media/data/manifest.json')}}">
<!-- Remove phone, date, address and email as default links -->
<meta name="format-detection" content="telephone=no">
<meta name="format-detection" content="date=no">
<meta name="format-detection" content="address=no">
<meta name="format-detection" content="email=no">
<meta name="theme-color" content="#37393e">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<!-- iDevice splash screens -->
<link href="{{asset('assets/img/splashscreens/iphone6_splash.png')}}"
    media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)"
    rel="apple-touch-startup-image">
<link href="{{asset('assets/img/splashscreens/iphoneplus_splash.png')}}"
    media="(device-width: 621px) and (device-height: 1104px) and (-webkit-device-pixel-ratio: 3)"
    rel="apple-touch-startup-image">
<link href="{{asset('assets/img/splashscreens/iphonex_splash.png')}}"
    media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)"
    rel="apple-touch-startup-image">
<link href="{{asset('assets/img/splashscreens/iphonexr_splash.png')}}"
    media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2)"
    rel="apple-touch-startup-image">
<link href="{{asset('assets/img/splashscreens/iphonexsmax_splash.png')}}"
    media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3)"
    rel="apple-touch-startup-image">
<link href="{{asset('assets/img/splashscreens/ipad_splash.png')}}"
    media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2)"
    rel="apple-touch-startup-image">
<link href="{{asset('assets/img/splashscreens/ipadpro1_splash.png')}}"
    media="(device-width: 834px) and (device-height: 1112px) and (-webkit-device-pixel-ratio: 2)"
    rel="apple-touch-startup-image">
<link href="{{asset('assets/img/splashscreens/ipadpro3_splash.png')}}"
    media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2)"
    rel="apple-touch-startup-image">
<link href="{{asset('assets/img/splashscreens/ipadpro2_splash.png')}}"
    media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2)"
    rel="apple-touch-startup-image">
<!-- Remove Tap Highlight on Windows Phone IE -->
<meta name="msapplication-tap-highlight" content="no">
<!-- base css -->
<link id="vendorsbundle" rel="stylesheet" media="screen, print" href="{{asset('assets/css/vendors.bundle.css')}}">
<link id="appbundle" rel="stylesheet" media="screen, print" href="{{asset('assets/css/app.bundle.css')}}">
<link id="mytheme" rel="stylesheet" media="screen, print" href="#">
<link id="myskin" rel="stylesheet" media="screen, print" href="{{asset('assets/css/skins/skin-master.css')}}">

<!-- Place favicon.ico in the root directory -->
<link rel="apple-touch-icon" sizes="180x180" href="{{asset('assets/img/favicon/apple-touch-icon.png')}}">
<link rel="icon" type="image/png" sizes="32x32" href="{{asset('assets/img/favicon/favicon-32x32.html')}}">
<link rel="mask-icon" href="{{asset('assets/img/favicon/safari-pinned-tab.svg')}}" color="#5bbad5">
<link rel="stylesheet" media="screen, print" href="{{asset('assets/css/theme-demo.css')}}">

<link rel="stylesheet" media="screen, print" href="{{asset('assets/css/datagrid/datatables/datatables.bundle.css')}}">

<!-- main css -->
<link rel="stylesheet" href="{{asset('assets/css/main-css.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/admin-dashboard/style.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/super-admin-dashboard/style.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/bookmap/style.css')}}">


<!-- font awesome icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<!-- select2 -->
<link rel="stylesheet" media="screen, print" href="{{asset('assets/css/formplugins/select2/select2.bundle.css')}}">
<!-- fancybox -->
<!-- Fancybox CSS -->
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css">

<!-- Fancybox JS -->
<script src="https://cdn.jsdelivr.net/npm/@fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>

<!-- bootstrap Icon -->
 <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
<style>
    label:has(+ input:required):after {
        content: ' *';
        color: red;
    }

    label:has(+ select:required):after {
        content: ' *';
        color: red;
    }

    .form-group:has(input:required)>label::after,
    .form-group:has(select:required)>label::after,
    .form-group:has(textarea:required)>label::after {
        content: " *";
        color: red;
        font-weight: bold;
    }

    .profile-initials {
        width: 40px;
        height: 40px;
        background-color: #5c6bc0;
        /* Google-like color */
        color: #fff;
        font-weight: 600;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-transform: uppercase;
    }
</style>