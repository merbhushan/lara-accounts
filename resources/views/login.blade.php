<!DOCTYPE html>
<html>
<head>
	<title>Login..</title>
</head>
<body>
	Please Wait... 
	<div id="test_div" style="display: none;">
            <img src="http://mem.meditab.test:8010/start/session?token={{$arrUserScopes[1]}}">
            <img src="http://mos.meditab.test:8020/start/session?token={{$arrUserScopes[2]}}">
	</div>
</body>
<script>
    window.onload=redirectToUrl();
    
    function redirectToUrl(){
        var strUrl = '{{ $strRedirectUrl }}';
//        clearCookies(strUrl);
        window.location = strUrl;
    }
</script>
</html>