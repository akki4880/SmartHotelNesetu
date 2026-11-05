 <body>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="SmartHotel - Complete Hotel Management Software. Manage room bookings, invoices, staff, billing, and more from one powerful platform." />
  <meta name="keywords" content="hotel management software, hotel PMS, room booking, hotel billing, employee management hotel" />
  <meta property="og:title" content="SmartHotel - Complete Hotel Management Solution" />
  <meta property="og:description" content="Streamline room booking, invoices, employee management, restaurant billing and housekeeping — all in one platform." />
  <meta property="og:type" content="website" />
  <title>@yield('title')</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <!-- Custom CSS -->
  <link rel="stylesheet" href="{{asset('frontend/assets/css/style.css')}}" />
</head>
<body>
	@include('frontend.layouts.header') 
	@yield('content')
	
	@include('frontend.layouts.footer')
</body>
</html>