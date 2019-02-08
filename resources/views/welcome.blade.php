<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>非凡舞韵生活家</title>

    <!-- Fonts -->

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 105vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .content img {
            width: 60%;
            height: 60%;
        }

        .title {
            font-size: 3em;
            padding: 0 10px;
        }
        .code{
            margin-bottom: 30px;
        }
        .links{
            font-weight: 500;
        }
        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    @if (Route::has('login'))
        <div class="top-right links">
            @auth
                <a href="{{ url('/home') }}">首页</a>
            @else
                <a href="{{ route('login') }}">登录</a>

                @if (Route::has('register'))
                    <a href="{{ route('register') }}">注册</a>
                @endif
            @endauth
        </div>
    @endif

    <div class="content">
        <div class="title m-b-md">
            非凡舞韵生活家
        </div>
        <img src="/images/miniprogramcode.jpg" alt="小程序码">
        <div class="code m-b-md">
            微信小程序
        </div>
        <div class="links m-b-md">
            青岛非凡舞韵健身有限公司主办
            <p>服务热线：0532-86897711</p>
        </div>
        <div class="footer">
            <p>© 2018-2022</p>
            <p>备案号：鲁ICP备11028443号-1</p>
        </div>

    </div>
</div>
</body>
</html>
