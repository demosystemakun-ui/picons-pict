<!DOCTYPE html> 
<html lang="en"> 
 
<head> 
    <meta charset="utf-8" /> 
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0" /> 
    <meta http-equiv="X-UA-Compatible" content="ie=edge" /> 
    <title>Loader Demo</title> 
    <style> 
        #loader { 
            border: 12px solid #f3f3f3; 
            border-radius: 50%; 
            border-top: 12px solid #444444; 
            width: 70px; 
            height: 70px; 
            animation: spin 1s linear infinite; 
        } 
         
        @keyframes spin { 
            100% { 
                transform: rotate(360deg); 
            } 
        } 
         
        .center { 
            position: absolute; 
            top: 0; 
            bottom: 0; 
            left: 0; 
            right: 0; 
            margin: auto; 
        } 
    </style> 
</head> 
 
<body> 
    <div id="loader" class="center"></div> 
    <h1>SistemIT.com</h1> 
    <h2>Tutorial PHP Pemula Sampai Mahir</h2> 
    <img src="https://www.sistemit.com/wp-content/uploads/2020/02/SISTEMITCOM-smlest.png"
        alt="Logo Sistem IT" /> <br>
    <img src="https://www.sistemit.com/wp-content/uploads/2020/02/Header-Baru-1.png"
        alt="Logo Sistem IT" /> 
    <script> 
        document.onreadystatechange = function() { 
            if (document.readyState !== "complete") { 
                document.querySelector( 
                "body").style.visibility = "hidden"; 
                document.querySelector( 
                "#loader").style.visibility = "visible"; 
            } else { 
                document.querySelector( 
                "#loader").style.display = "none"; 
                document.querySelector( 
                "body").style.visibility = "visible"; 
            } 
        }; 
    </script> 
</body> 
</html> 