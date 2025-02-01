<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
	<link href="https://vjs.zencdn.net/8.3.0/video-js.css" rel="stylesheet">
<script src="https://vjs.zencdn.net/8.3.0/video.min.js"></script>
</head>
<body>
<video id="my-video" class="video-js" controls preload="auto" autoplay muted playsinline style="position: absolute;top: 0;left: 0;bottom: 0;right: 0;z-index: 99999999;object-fit:;height: 100vh;width: 100vw;">
    <source src="img/Composition1.mov" type="video/mp4">
    Votre navigateur ne prend pas en charge cette vidéo.
</video>

<script>
    var player = videojs('my-video');
</script>

</body>
</html>