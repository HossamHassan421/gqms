<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <style>
        body{
            padding: 0;
            margin: 0;
            text-align: center;
            font-family: Arial;
            background-color: #34495e;
        }
        .container{
            display: inline-block;
            margin-top: 10%;
            font-size: 3rem;
            color: #FFFFFF;
        }
        #section{
            background-color: #ffffff;
            color: #2c3e50;
            font-size: 4rem;
            margin-top: 10px;
            font-weight: bold;
            padding: 2rem;
            border-radius: 5px;
            /*box-shadow: 3px 3px 3px #CCCCCC;*/
            /*border: 1px solid #555555;*/
        }
    </style>

    <title>Current IP</title>
</head>
<body>
    <div class="container">
        Device IP is
        <div id="section">
{{--            {{ getHostName() }}--}}
{{--            {{ getHostByName(getHostName()) }}--}}
        </div>
    </div>

    <script type="text/javascript">
        function saveIpInSession() {
            // NOTE: window.RTCPeerConnection is "not a constructor" in FF22/23
            var RTCPeerConnection = /*window.RTCPeerConnection ||*/ window.webkitRTCPeerConnection || window.mozRTCPeerConnection;

            if (RTCPeerConnection) (function () {
                var rtc = new RTCPeerConnection({iceServers: []});
                if (1 || window.mozRTCPeerConnection) {      // FF [and now Chrome!] needs a channel/stream to proceed
                    rtc.createDataChannel('', {reliable: false});
                }
                console.log(rtc);
                rtc.onicecandidate = function (evt) {
                    // convert the candidate to SDP so we can run it through our general parser
                    // see https://twitter.com/lancestout/status/525796175425720320 for details
                    if (evt.candidate) grepSDP("a=" + evt.candidate.candidate);
                };
                rtc.createOffer(function (offerDesc) {
                    grepSDP(offerDesc.sdp);
                    rtc.setLocalDescription(offerDesc);
                }, function (e) {
                    console.warn("offer failed", e);
                });


                var addrs = Object.create(null);
                addrs["0.0.0.0"] = false;

                function updateDisplay(newAddr) {
                    if (newAddr in addrs) return;
                    else addrs[newAddr] = true;
                    var displayAddrs = Object.keys(addrs).filter(function (k) {
                        return addrs[k];
                    });
                    var ip = '';
                    if(displayAddrs[0].length >= 20) {
                            @php
                                $ip = getenv_get_client_ip();
                            @endphp
                        ip = '{{$ip}}'
                    } else {
                        ip = displayAddrs[0];
                    }
                    document.getElementById('section').innerHTML = ip;
                    // document.getElementById('section').innerHTML = displayAddrs[0];
                }

                function grepSDP(sdp) {

                    var hosts = [];
                    sdp.split('\r\n').forEach(function (line) { // c.f. http://tools.ietf.org/html/rfc4566#page-39
                        if (~line.indexOf("a=candidate")) {     // http://tools.ietf.org/html/rfc4566#section-5.13
                            var parts = line.split(' '),        // http://tools.ietf.org/html/rfc5245#section-15.1
                                addr = parts[4],
                                type = parts[7];
                            if (type === 'host') updateDisplay(addr);
                        } else if (~line.indexOf("c=")) {       // http://tools.ietf.org/html/rfc4566#section-5.7
                            var parts = line.split(' '),
                                addr = parts[2];
                            updateDisplay(addr);
                        }
                    });
                }
            })();
        }
        saveIpInSession();


    </script>
</body>
</html>
