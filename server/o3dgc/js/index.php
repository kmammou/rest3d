
<!DOCTYPE html>
<html lang="en">
    <head>
        <title> o3dgc decoding</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
        <style>
            body {
                color: #cccccc;
                font-family:Monospace;
                font-size:13px;
                text-align:center;

                background-color: #050505;
                margin: 0px;
                overflow: hidden;
            }
            #info {
                position: absolute;
                top: 0px; width: 100%;
                padding: 5px;
            }
            a {
                color: #0080ff;
            }
        </style>

        <script>
        var container;
        var camera, scene, renderer;
        var geometry, mesh, ifs, material;
        var oReq = new XMLHttpRequest();
        var D = 100;
        var compressedStream;
        var resType = "arraybuffer";
<?php
    if (isset($_GET["model"]) && $_GET["model"] != ''){
        echo "        var fileName = '".$_GET["model"]."';\n";
    }
    else
    {
        echo "        var fileName = 'duck.s3d';\n";
    }
?>
        if (fileName.indexOf("ascii") !== -1){
            resType = "text";
        }
        oReq.open("GET", fileName, true);
        oReq.responseType = resType;
        oReq.onload = function (oEvent) {
            compressedStream = oReq.response;
        };
        oReq.send(null);
        </script>
        <script type="text/javascript" src="o3dgc.js"></script>
        <script>
        function decode(arrayBuffer) {
            if (arrayBuffer) {
                function str2ab(str) {
                    var buf = new ArrayBuffer(str.length);
                    var bufView = new Uint8Array(buf);
                    for (var i=0, strLen=str.length; i<strLen; i++) {
                        bufView[i] = str.charCodeAt(i);
                    }
                    return buf;
                }
                if (resType === "text") {
                    var bstream = new o3dgc.BinaryStream(str2ab(arrayBuffer));
                }
                else{
                    var bstream = new o3dgc.BinaryStream(arrayBuffer);
                }
                var decoder = new o3dgc.SC3DMCDecoder();
                var timer = new o3dgc.Timer();
                ifs = new o3dgc.IndexedFaceSet();
                timer.Tic();
                decoder.DecodeHeader(ifs, bstream);
                timer.Toc();
                console.log("DecodeHeader time (ms) " + timer.GetElapsedTime());
                // allocate memory
                var byteSize = 3 * 2 * ifs.GetNCoordIndex() +
                               3 * 4 * ifs.GetNCoord() +
                               3 * 4 * ifs.GetNNormal() +
                               3 * 4 * ifs.GetNColor() +
                               2 * 4 * ifs.GetNTexCoord();
                var buffer = new ArrayBuffer(byteSize);
                var shift = 0;
                if (ifs.GetNCoordIndex() > 0) {
                    ifs.SetCoordIndex(new Int16Array(buffer, shift, 3 * ifs.GetNCoordIndex()));
                    shift += 6 * ifs.GetNCoordIndex();
                }
                if (ifs.GetNCoord() > 0) {
                    var nc = 3 * ifs.GetNCoord();
                    ifs.SetCoord(new Float32Array(buffer, shift, nc));
                    shift += 12 * ifs.GetNCoord();
                }
                if (ifs.GetNNormal() > 0) {
                    ifs.SetNormal(new Float32Array(buffer, shift, 3 * ifs.GetNNormal()));
                    shift += 12 * ifs.GetNNormal();
                }
                if (ifs.GetNColor() > 0) {
                    ifs.SetColor(new Float32Array(buffer, shift, 3 * ifs.GetNColor()));
                    shift += 12 * ifs.GetNColor();
                }
                if (ifs.GetNTexCoord() > 0) {
                    ifs.GetTexCoord(new Float32Array(buffer, shift, 2 * ifs.GetNTexCoord()));
                    shift += 8 * ifs.GetNTexCoord();
                }
                console.log("Mesh info ");
                console.log("\t# coords    " + ifs.GetNCoord());
                console.log("\t# normals   " + ifs.GetNNormal());
                console.log("\t# texcoords " + ifs.GetNTexCoord());
                console.log("\t# triangles " + ifs.GetNCoordIndex());
                // decode mesh
                timer.Tic();
                decoder.DecodePlayload(ifs, bstream);
                timer.Toc();
                var size = arrayBuffer.byteLength;
                console.log("DecodePlayload time " + timer.GetElapsedTime() + " ms, " + size + " bytes (" + (8.0 * size / ifs.GetNCoord()) + " bpv)");
                console.log("Details");
                var stats = decoder.GetStats();
                console.log("\t CoordIndex         " + stats.m_timeCoordIndex + " ms, " + stats.m_streamSizeCoordIndex + " bytes (" + (8.0 * stats.m_streamSizeCoordIndex / ifs.GetNCoord()) + " bpv)");
                console.log("\t Coord              " + stats.m_timeCoord + " ms, " + stats.m_streamSizeCoord + " bytes (" + (8.0 * stats.m_streamSizeCoord / ifs.GetNCoord()) + " bpv)");
                console.log("\t Normal             " + stats.m_timeNormal + " ms, " + stats.m_streamSizeNormal + " bytes (" + (8.0 * stats.m_streamSizeNormal / ifs.GetNCoord()) + " bpv)");
                console.log("\t TexCoord           " + stats.m_timeTexCoord + " ms, " + stats.m_streamSizeTexCoord + " bytes (" + (8.0 * stats.m_streamSizeTexCoord / ifs.GetNCoord()) + " bpv)");
                console.log("\t Color              " + stats.m_timeColor + " ms, " + stats.m_streamSizeColor + " bytes (" + (8.0 * stats.m_streamSizeColor / ifs.GetNCoord()) + " bpv)");
                console.log("\t Float Attributes   " + stats.m_timeFloatAttribute + " ms, " + stats.m_streamSizeFloatAttribute + " bytes (" + (8.0 * stats.m_streamSizeFloatAttribute / ifs.GetNCoord()) + " bpv)");
                console.log("\t Integer Attributes " + stats.m_timeFloatAttribute + " ms, " + stats.m_streamSizeFloatAttribute + " bytes (" + (8.0 * stats.m_streamSizeFloatAttribute / ifs.GetNCoord()) + " bpv)");
                console.log("\t Reorder            " + stats.m_timeReorder + " ms,  " + 0 + " bytes (" + 0.0 + " bpv)");
                //SaveOBJ(ifs, fileName);
            }
        }
        </script>
        <script src="three.min.js"></script>
    </head>
    <body>

        <div id="container"></div>
        <div id="info"><a href="https://github.com/amd/rest3d/tree/master/server/o3dgc" target="_blank">o3dgc</a> compressed stream decoding </div>
        <script>
        init();
        animate();
        function init() {
            container = document.getElementById('container');
            camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 1000.0);
            camera.position.z = 300;
            scene = new THREE.Scene();
            scene.add(new THREE.AmbientLight(0x444444));
            var light1 = new THREE.DirectionalLight(0xffffff, 0.5);
            light1.position.set(1, 1, 1);
            scene.add(light1);

            var light2 = new THREE.DirectionalLight(0xffffff, 1.5);
            light2.position.set(0, -1, 0);
            scene.add(light2);

            material = new THREE.MeshPhongMaterial({
                color: 0xaabbcc, ambient: 0xaaaaaa, specular: 0xffffff, shininess: 250,
                side: THREE.DoubleSide, vertexColors: THREE.VertexColors
            });
            renderer = new THREE.WebGLRenderer({ antialias: false, alpha: false });
            renderer.setClearColor(0xaaaaaa, 1);
            renderer.setSize(window.innerWidth, window.innerHeight);
            renderer.gammaInput = true;
            renderer.gammaOutput = true;
            renderer.physicallyBasedShading = true;
            container.appendChild(renderer.domElement);
            window.addEventListener('resize', onWindowResize, false);
        }
        function onWindowResize() {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        }
        function animate() {
            requestAnimationFrame(animate);
            render();
        }
        function render() {
            if (mesh !== undefined) {
                var time = Date.now() * 0.001;
                mesh.rotation.x = time * 0.25;
                mesh.rotation.y = time * 0.5;
            }
            else if (compressedStream !== undefined && compressedStream) {
                decode(compressedStream);
                var n = 3 * ifs.GetNCoord();
                geometry = new THREE.BufferGeometry();
                geometry.attributes = [];
                if (ifs.GetNCoordIndex() > 0) {
                    geometry.attributes.index = { itemSize: 1, array: ifs.GetCoordIndex(), numItems: ifs.GetNCoordIndex() * 3 };
                    geometry.offsets = [{ start: 0, index: 0, count: ifs.GetNCoordIndex() * 3}];
                }
                if (ifs.GetNCoord() > 0) {
                    geometry.attributes.position = { itemSize: 3, array: ifs.GetCoord(), numItems: ifs.GetNCoord() * 3 };
                }
                if (ifs.GetNNormal() > 0) {
                    geometry.attributes.normal = { itemSize: 3, array: ifs.GetNormal(), numItems: ifs.GetNNormal() * 3 };
                }
                if (ifs.GetNColor() > 0) {
                    geometry.attributes.color = { itemSize: 3, array: ifs.GetColor(), numItems: ifs.GetNColor() * 3 };
                }
                else {
                    var colors = new Float32Array(n);
                    for(var i = 0; i < n; i+=3){
                        colors[i] = 1.0;
                        colors[i+1] = 1.0;
                        colors[i+2] = 0.0;
                    }
                    geometry.attributes.color = { itemSize: 3, array: colors, numItems: n };
                }
                geometry.computeBoundingSphere();
                // center and scale
                var c = geometry.boundingSphere.center;
                var r = geometry.boundingSphere.radius;
                for (var i = 0; i < n; i += 3) {
                    geometry.attributes.position.array[i] = D * (geometry.attributes.position.array[i] - c.x) / r;
                    geometry.attributes.position.array[i + 1] = D * (geometry.attributes.position.array[i + 1] - c.y) / r;
                    geometry.attributes.position.array[i + 2] = D * (geometry.attributes.position.array[i + 2] - c.z) / r;
                }
                mesh = new THREE.Mesh(geometry, material);

                scene.add(mesh);
                meshAddedToScene = true;
            }
            renderer.render(scene, camera);
        }
        function SaveOBJ(ifs, fileName) {
            triangles = ifs.GetCoordIndex();
            points = ifs.GetCoord();
            normals = ifs.GetNormal();
            texCoords = ifs.GetTexCoord();
            var np = ifs.GetNCoord();
            var nn = ifs.GetNNormal();
            var nt = ifs.GetNTexCoord();
            var nf = ifs.GetNCoordIndex();
            document.writeln("#### <br>");
            document.writeln("# <br>");
            document.writeln("# OBJ File Generated by test_o3dgc<br>");
            document.writeln("#<br>");
            document.writeln("####<br>");
            document.writeln("# Object " + fileName + "<br>");
            document.writeln("#<br>");
            document.writeln("# Coord:     " + np + "<br>");
            document.writeln("# Normals:   " + nn + "<br>");
            document.writeln("# TexCoord:  " + nt + "<br>");
            document.writeln("# Triangles: " + nf + "<br>");
            document.writeln("#<br>");
            document.writeln("####<br>");
            for (var i = 0; i < np; ++i) {
                document.writeln("v " + points[3 * i] + " " + points[3 * i + 1] + " " + points[3 * i + 2] + "<br>");
            }
            for (var i = 0; i < nn; ++i) {
                document.writeln("vn " + normals[3 * i] + " " + normals[3 * i + 1] + " " + normals[3 * i + 2] + "<br>");
            }
            for (var i = 0; i < nt; ++i) {
                document.writeln("vt " + texCoords[2 * i] + " " + texCoords[2 * i + 1] + "<br>");
            }
            if (nt > 0 && nn > 0) {
                for (var i = 0; i < nf; ++i) {
                    document.writeln("f " + (triangles[3 * i] + 1) + "/" + (triangles[3 * i] + 1) + "/" + (triangles[3 * i] + 1) + " " + (triangles[3 * i + 1] + 1) + "/" + (triangles[3 * i + 1] + 1) + "/" + (triangles[3 * i + 1] + 1) + " " + (triangles[3 * i + 2] + 1) + "/" + (triangles[3 * i + 2] + 1) + "/" + (triangles[3 * i + 2] + 1) + "<br>");
                }
            }
            else if (nt == 0 && nn > 0) {
                for (var i = 0; i < nf; ++i) {
                    document.writeln("f " + (triangles[3 * i] + 1) + "//" + (triangles[3 * i] + 1) + " " + (triangles[3 * i + 1] + 1) + "//" + (triangles[3 * i + 1] + 1) + " " + (triangles[3 * i + 2] + 1) + "//" + (triangles[3 * i + 2] + 1) + "<br>");
                }
            }
            else if (nt > 0 && nn == 0) {
                for (var i = 0; i < nf; ++i) {
                    document.writeln("f " + (triangles[3 * i] + 1) + "/" + (triangles[3 * i] + 1) + " " + triangles[3 * i + 1] + 1 + "/" + (triangles[3 * i + 1] + 1) + " " + (triangles[3 * i + 2] + 1) + "/" + (triangles[3 * i + 2] + 1) + "<br>");
                }
            }
            else {
                for (var i = 0; i < nf; ++i) {
                    document.writeln("f " + (triangles[3 * i] + 1) + " " + (triangles[3 * i + 1] + 1) + " " + (triangles[3 * i + 2] + 1) + "<br>");
                }
            }
        }
        </script>
    </body>
</html>