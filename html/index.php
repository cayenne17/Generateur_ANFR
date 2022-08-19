<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Nouvelles déclarations auprès de l'ANFR">
	<meta name="generator" content="Bluefish 2.2.11" >
	<title>Nouvelles déclarations ANFR</title>
	<link rel="icon" type="image/x-icon" href="./favicon.ico">
	
	<!-- disable cache -->
	<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Expires" content="0" />

	<!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.8.0/dist/leaflet.css" integrity="sha512-hoalWLoI8r4UszCkZ5kL8vayOGVae1oxXe/2A4AO6J9+580uKHDO3JdHb7NzwwzK5xr/Fs0W40kiNHxM9vyTtQ==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.8.0/dist/leaflet.js" integrity="sha512-BB3hKbKWOc9Ez/TAwyWxNXeoV9c1v6FIeYiBieIWkpLjauysF18NzgR1MBNBXf8/KABdlkX68nAhlwcDFLGPCQ==" crossorigin=""></script>

	<!-- MarkerCluster -->	
	<link rel="stylesheet" href="./assets/js/MarkerCluster/MarkerCluster.css" />
	<link rel="stylesheet" href="./assets/js/MarkerCluster/MarkerCluster.Default.css" />
	<script src="./assets/js/MarkerCluster/leaflet.markercluster.js"></script>

	<style>
		html, body {
			height: 100%;
			margin: 0;
		}
		
		.info { padding: 6px 8px; font: 12px/14px Arial, Helvetica, sans-serif; background: white; background: rgba(255,255,255,0.8); box-shadow: 0 0 15px rgba(0,0,0,0.2); border-radius: 5px; }
		.legend { text-align: left; line-height: 18px; color: #333; }
		.legend i { width: 18px; height: 18px; float: left; margin-right: 4px; opacity: 0.7; }
		.legend br { clear: both; } <!-- fix bug chrome(imun) -->
	</style>
</head>

<body>

    <div id="map" style="width: 100%; height: 70vh; border: 1px solid #ccc;"></div>
    <span><br><b>Modifications ANFR hebdomadaires</b>
		<br>Mise à jour <span id='dataset'></span> du <span id='update'></span>&nbsp;&nbsp;-> Source : <a href="https://data.anfr.fr/anfr/visualisation/export/?id=observatoire_2g_3g_4g" >Opendata Observatoire ANFR</a>
		<br>
		<br>4G: <span id='news'></span> fréquences ajoutées, <span id='acti'></span> fréquences activées
	    <br>4G: <span id='supp'></span> fréquences supprimées, <span id='off'></span> fréquences éteintes
	    <br>5G: <span id='news5'></span> fréquences ajoutées, <span id='acti5'></span> fréquences activées
	   	<br>5G: <span id='supp5'></span> fréquences supprimées, <span id='off5'></span> fréquences éteintes
	</span>
	
	<script src="./assets/js/jquery-min.js"></script>
	<?php	
		if (empty($_GET["v"])){
			$path_script_diff='./assets/data/diff.js';
		} else {
			$path_script_diff='./assets/data/' . $_GET["v"] . '_diff.js';
		}
	?>
	<script src="<?php echo "$path_script_diff";?>"></script>
		
	<script type="text/javascript">


		// Plan IGN
		var PlanIGN = L.tileLayer('https://wxs.ign.fr/{ignApiKey}/geoportail/wmts?'+'&REQUEST=GetTile&SERVICE=WMTS&VERSION=1.0.0&TILEMATRIXSET=PM'+'&LAYER={ignLayer}&STYLE={style}&FORMAT={format}'+'&TILECOL={x}&TILEROW={y}&TILEMATRIX={z}',	{
			ignApiKey: 'decouverte',
			ignLayer: 'GEOGRAPHICALGRIDSYSTEMS.PLANIGNV2',
			style: 'normal',
			format: 'image/png',
			service: 'WMTS',
			attribution: '&copy; <a target="_blank" rel="noreferrer noopener" href="https://www.ign.fr"><img class="gp-control-attribution-image" src="https://wxs.ign.fr/static/logos/IGN/IGN.gif" title="Institut national de l\'information géographique et forestière" style="height: 30px; width: 30px;"></a> '
		});

		// Photographies aériennes Ortho IGN
		var OrthoIGN = L.tileLayer('https://wxs.ign.fr/{ignApiKey}/geoportail/wmts?'+'&REQUEST=GetTile&SERVICE=WMTS&VERSION=1.0.0&TILEMATRIXSET=PM'+'&LAYER={ignLayer}&STYLE={style}&FORMAT={format}'+'&TILECOL={x}&TILEROW={y}&TILEMATRIX={z}', {
			ignApiKey: 'decouverte',
			ignLayer: 'ORTHOIMAGERY.ORTHOPHOTOS',
			style: 'normal',
			format: 'image/jpeg',
			service: 'WMTS',
			attribution: '&copy; <a target="_blank" rel="noreferrer noopener" href="https://www.ign.fr"><img class="gp-control-attribution-image" src="https://wxs.ign.fr/static/logos/IGN/IGN.gif" title="Institut national de l\'information géographique et forestière" style="height: 30px; width: 30px;"></a> '
		});

		// OSM FR
		var OSM_FR = L.tileLayer('//{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
			minZoom: 3,
			maxZoom: 18,
			attribution: 'donn&eacute;es &copy; <a href="//osm.org/copyright">OpenStreetMap</a>/ODbL - rendu <a href="//openstreetmap.fr">OSM France</a> ',
		});

		// OSM Default
		var OSM = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			minZoom: 3,
			maxZoom: 18,
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors '
		});

		Lat="<?php echo $_GET['lat'];?>";   Lon="<?php echo $_GET['lon'];?>";   Zoom = "<?php echo $_GET['zoom'];?>"; //definition variable GET lat, lon, zoom
		if (Lat.length === 0 || isNaN(Lat) || Lon.length === 0 || isNaN(Lon) || Zoom.length === 0 || isNaN(Zoom)){
			latlng = L.latLng(46.5, 2);
			Zoom = 6;
		}else{
			latlng = L.latLng(Lat, Lon);
		}
		
		var map = L.map('map', {center: latlng, zoom: Zoom, layers: [OSM]});

		var baseLayers = {
			"OSM": OSM,
			"OSM FR": OSM_FR,
			"Plan IGN": PlanIGN,
			"Ortho IGN": OrthoIGN
		};
		L.control.layers(baseLayers).addTo(map);
		L.control.scale({metric: true, imperial: false}).addTo(map);	// Ajouter l'échelle 
		
		
		var queryParams = new URLSearchParams(window.location.search);
		queryParams.set("lat", map.getCenter().lat.toFixed(4)); queryParams.set("lon", map.getCenter().lng.toFixed(4));
		queryParams.set("zoom", map.getZoom());
		history.replaceState(null, null, "?"+queryParams.toString());
		
		map.on("moveend", function (e) { queryParams.set("lat", map.getCenter().lat.toFixed(4)); queryParams.set("lon", map.getCenter().lng.toFixed(4)); history.replaceState(null, null, "?"+queryParams.toString());}); // à chaque évenement de mouvement de carte, mettre à jour les variable get 'lat' et 'lon'.
		map.on("zoomend", function (e) { queryParams.set("zoom", map.getZoom()); history.replaceState(null, null, "?"+queryParams.toString());}); // à chaque évenement de zoom sur la carte, mettre à jour la variable get zoom.

		//custom icon
        var sfrIcon = L.icon({
        	iconUrl: './assets/images/marker_red.png',
        	shadowUrl: './assets/images/marker-shadow.png',
		iconSize: [26, 42],
        	iconAnchor: [12, 41],
        	shadowAnchor: [12, 41],
		popupAnchor: [0, -36]
        });

        var blueIcon = L.icon({
        	iconUrl: './assets/images/marker_blue.png',
        	shadowUrl: './assets/images/marker-shadow.png',
		iconSize: [26, 42],
        	iconAnchor: [12, 41],
        	shadowAnchor: [12, 41],
		popupAnchor: [0, -36]
        });

        var freeIcon = L.icon({
        	iconUrl: './assets/images/marker_grey.png',
        	shadowUrl: './assets/images/marker-shadow.png',
		iconSize: [26, 42],
        	iconAnchor: [12, 41],
        	shadowAnchor: [12, 41],
		popupAnchor: [0, -36]
        });

	var orangeIcon = L.icon({
        	iconUrl: './assets/images/marker_orange.png',
        	shadowUrl: './assets/images/marker-shadow.png',
		iconSize: [26, 42],
        	iconAnchor: [12, 41],
        	shadowAnchor: [12, 41],
		popupAnchor: [0, -36]
        });

        var blackIcon = L.icon({
        	iconUrl: './assets/images/marker_black.png',
        	shadowUrl: './assets/images/marker-shadow.png',
		iconSize: [26, 42],
        	iconAnchor: [12, 41],
        	shadowAnchor: [12, 41],
		popupAnchor: [0, -36]
        });
        
        
        var ripRed = L.icon({
        	iconUrl: './assets/images/rip_red.png',
        	shadowUrl: './assets/images/marker-shadow.png',
        	iconAnchor: [12, 41],
        	shadowAnchor: [12, 41],
		popupAnchor: [0, -36]
        });

        var ripBlue = L.icon({
        	iconUrl: './assets/images/rip_blue.png',
        	shadowUrl: './assets/images/marker-shadow.png',
        	iconAnchor: [12, 41],
        	shadowAnchor: [12, 41],
		popupAnchor: [0, -36]
        });

        var ripGrey = L.icon({
        	iconUrl: './assets/images/rip_grey.png',
        	shadowUrl: './assets/images/marker-shadow.png',
        	iconAnchor: [12, 41],
        	shadowAnchor: [12, 41],
		popupAnchor: [0, -36]
        });

		var ripOrange = L.icon({
        	iconUrl: './assets/images/rip_orange.png',
        	shadowUrl: './assets/images/marker-shadow.png',
        	iconAnchor: [12, 41],
        	shadowAnchor: [12, 41],
		popupAnchor: [0, -36]
        });

        var ripBlack = L.icon({
        	iconUrl: './assets/images/rip_black.png',
        	shadowUrl: './assets/images/marker-shadow.png',
        	iconAnchor: [12, 41],
        	shadowAnchor: [12, 41],
		popupAnchor: [0, -36]
        });

		var markers = L.markerClusterGroup({
			chunkedLoading: true,
			maxClusterRadius: 40	//réduit la superficie à clusteriser mais plus lent ! ; par défaut à 80
		});

		for (var i = 0; i < addressPoints.length; i++) {
			var a = addressPoints[i];
			var title;
			var icone;
			if (a[7]==0) {
				title = "Support <a href='https://data.anfr.fr/anfr/visualisation/map/?id=observatoire_2g_3g_4g&refine.SUP_ID=" +a[3]+ "' target='_blank' rel='noreferrer noopener'>#" +a[3]+ "</a> &#160&#160 <a href='https://data.anfr.fr/anfr/visualisation/map/?id=observatoire_2g_3g_4g&location=18," +a[0]+ "," +a[1]+ "' target='_blank' rel='noreferrer noopener'>data.anfr.fr</a> &#160&#160 <a href='https://www.cartoradio.fr/index.html#/cartographie/lonlat/" +a[1]+ "/" +a[0] + "' target='_blank' rel='noreferrer noopener'>Cartoradio</a><br>"+a[4]+ "<br>Suppression: "+a[5]+ "<br>Extinction: "+a[6];
				if (a[2]==20820) {
					icone = ripBlue;
				} else if (a[2]==20810) {
					icone = ripRed;
				} else if (a[2]==20801) {
					icone = ripOrange;
				} else if (a[2]==20815) {
					title = "Support <a href='https://data.anfr.fr/anfr/visualisation/map/?id=observatoire_2g_3g_4g&refine.SUP_ID=" +a[3]+ "' target='_blank' rel='noreferrer noopener'>#" +a[3]+ "</a> &#160&#160 <a href='https://data.anfr.fr/anfr/visualisation/map/?id=observatoire_2g_3g_4g&location=18," +a[0]+ "," +a[1]+ "' target='_blank' rel='noreferrer noopener'>data.anfr.fr</a> &#160&#160 <a href='https://www.cartoradio.fr/index.html#/cartographie/lonlat/" +a[1]+ "/" +a[0] + "' target='_blank' rel='noreferrer noopener'>Cartoradio</a>&#160&#160 <a href='https://rncmobile.net/site/" +a[0]+ "," +a[1] + "' target='_blank' rel='noreferrer noopener'>RNCMobile</a><br>"+a[4]+ "<br>Suppression: "+a[5]+ "<br>Extinction: "+a[6];
					icone = ripGrey;
				} else {
					icone = ripBlack;
				}
			} else {
				title = "Support <a href='https://data.anfr.fr/anfr/visualisation/map/?id=observatoire_2g_3g_4g&refine.SUP_ID=" +a[3]+ "' target='_blank' rel='noreferrer noopener'>#" +a[3]+ "</a> &#160&#160 <a href='https://data.anfr.fr/anfr/visualisation/map/?id=observatoire_2g_3g_4g&location=18," +a[0]+ "," +a[1]+ "' target='_blank' rel='noreferrer noopener'>data.anfr.fr</a> &#160&#160 <a href='https://www.cartoradio.fr/index.html#/cartographie/lonlat/" +a[1]+ "/" +a[0] + "' target='_blank' rel='noreferrer noopener'>Cartoradio</a><br>"+a[4]+ "<br>Nouveau: "+a[5]+ "<br>Activation: "+a[6];
				if (a[2]==20820) {
					icone = blueIcon;
				} else if (a[2]==20810) {
					icone = sfrIcon;
				} else if (a[2]==20801) {
					icone = orangeIcon;
				} else if (a[2]==20815) {
					title = "Support <a href='https://data.anfr.fr/anfr/visualisation/map/?id=observatoire_2g_3g_4g&refine.SUP_ID=" +a[3]+ "' target='_blank' rel='noreferrer noopener'>#" +a[3]+ "</a> &#160&#160 <a href='https://data.anfr.fr/anfr/visualisation/map/?id=observatoire_2g_3g_4g&location=18," +a[0]+ "," +a[1]+ "' target='_blank' rel='noreferrer noopener'>data.anfr.fr</a> &#160&#160 <a href='https://www.cartoradio.fr/index.html#/cartographie/lonlat/" +a[1]+ "/" +a[0] + "' target='_blank' rel='noreferrer noopener'>Cartoradio</a>&#160&#160 <a href='https://rncmobile.net/site/" +a[0]+ "," +a[1] + "' target='_blank' rel='noreferrer noopener'>RNCMobile</a><br>"+a[4]+ "<br>Nouveau: "+a[5]+ "<br>Activation: "+a[6];
					icone = freeIcon;
				} else {
					icone = blackIcon;
				}
			}

			var marker = L.marker(L.latLng(a[0], a[1]), { icon: icone, title: "Support #"+a[3]+" - "+a[4] });
			marker.bindPopup(title);
			markers.addLayer(marker);
		}
		map.addLayer(markers);
		
		var legend = L.control({position: 'bottomright'});
		legend.onAdd = function (map) {
			var div = L.DomUtil.create('div', 'info legend'),
				grades = [],
				labels = [],
				from, to;
			labels.push('<i style="background: #ec6f00 "></i> Orange');
			labels.push('<i style="background: #ff0000 "></i> SFR');
			labels.push('<i style="background: #6c6c6c "></i> Free');
			labels.push('<i style="background: #105ec8 "></i> ByTel');
			labels.push('<i style="background: #000000 "></i> Multi');
			div.innerHTML = labels.join('<br>');
			return div;
		};
		legend.addTo(map);

		document.getElementById("dataset").innerHTML = dataset;
		document.getElementById("update").innerHTML = update;
		document.getElementById("news").innerHTML = news;
		document.getElementById("acti").innerHTML = acti;
		document.getElementById("news5").innerHTML = news5;
		document.getElementById("acti5").innerHTML = acti5;
		document.getElementById("supp").innerHTML = supp;
		document.getElementById("off").innerHTML = off;
		document.getElementById("supp5").innerHTML = supp5;
		document.getElementById("off5").innerHTML = off5;
	</script>

</body>
</html>
