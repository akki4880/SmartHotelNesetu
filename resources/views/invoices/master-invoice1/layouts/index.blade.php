<!DOCTYPE html>
<html lang="zxx" dir="ltr"> 
    <head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>@yield('title')</title> 
	 @include('invoices.master-invoice1.layouts.header') 
</head>
   
<body>
	@yield('content')
	
	@include('invoices.master-invoice1.layouts.footer')
</body>
</html>