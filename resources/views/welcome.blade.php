<!DOCTYPE html>
<html>
    <head>
        <meta charset='utf-8'>
        <meta content='width=device-width, initial-scale=1.0' name='viewport'>
        <meta content='IE=edge,chrome=1' http-equiv='X-UA-Compatible'>
        <meta content='index, follow' name='robots'>

        <title>Nanobox Cloud Provider - Proxmox</title>
        <meta content='Launch your Nanobox app on a Proxmox host.' name='description'>

        <!-- Typekit -->
        <script src='https://use.typekit.net/cqd5kth.js'></script>
        <script>
            try{Typekit.load({ async: true });}catch(e){}
        </script>
        <!-- Google Analytics -->
        <script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

            ga('create', 'UA-78086337-1', 'auto');
            ga('send', 'pageview');
        </script>
        <!-- start Mixpanel -->
        <script>
          (function(e,a){if(!a.__SV){var b=window;try{var c,l,i,j=b.location,g=j.hash;c=function(a,b){return(l=a.match(RegExp(b+"=([^&]*)")))?l[1]:null};g&&c(g,"state")&&(i=JSON.parse(decodeURIComponent(c(g,"state"))),"mpeditor"===i.action&&(b.sessionStorage.setItem("_mpcehash",g),history.replaceState(i.desiredHash||"",e.title,j.pathname+j.search)))}catch(m){}var k,h;window.mixpanel=a;a._i=[];a.init=function(b,c,f){function e(b,a){var c=a.split(".");2==c.length&&(b=b[c[0]],a=c[1]);b[a]=function(){b.push([a].concat(Array.prototype.slice.call(arguments,
          0)))}}var d=a;"undefined"!==typeof f?d=a[f]=[]:f="mixpanel";d.people=d.people||[];d.toString=function(b){var a="mixpanel";"mixpanel"!==f&&(a+="."+f);b||(a+=" (stub)");return a};d.people.toString=function(){return d.toString(1)+".people (stub)"};k="disable time_event track track_pageview track_links track_forms register register_once alias unregister identify name_tag set_config reset people.set people.set_once people.increment people.append people.union people.track_charge people.clear_charges people.delete_user".split(" ");
          for(h=0;h<k.length;h++)e(d,k[h]);a._i.push([b,c,f])};a.__SV=1.2;b=e.createElement("script");b.type="text/javascript";b.async=!0;b.src="undefined"!==typeof MIXPANEL_CUSTOM_LIB_URL?MIXPANEL_CUSTOM_LIB_URL:"file:"===e.location.protocol&&"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//)?"https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js":"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js";c=e.getElementsByTagName("script")[0];c.parentNode.insertBefore(b,c)}})(document,window.mixpanel||[]);
          mixpanel.init("d8c3c9862308c425024430c1f9840015");
        </script>
        <!-- end Mixpanel -->

        <link href="https://docs.nanobox.io/stylesheets/all-64831a2d.css" rel="stylesheet" type="text/css" />
        <script src="https://docs.nanobox.io/javascripts/all-5b225ba7.js" type="text/javascript"></script>

        <!-- Mixpanel Doc feedback -->
        <script>
          (function() {
            $(function() {
              $("#feedback").find("a").click((function(_this) {
                return function(e) {
                  var $target;
                  $target = $(e.currentTarget);
                  $target.closest("#feedback").addClass("submitted");
                  return mixpanel.track("Doc Reviewed", {
                    title: $target.data("mp-title"),
                    path: $target.data("mp-path"),
                    helpful: $target.data("mp-response")
                  });
                };
              })(this));
              return $("a.no").click((function(_this) {
                return function(e) {
                  return $("#reach-out").addClass("show");
                };
              })(this));
            });

          }).call(this);
        </script>
    </head>
    <body>
        <div class='page-wrapper'>
            <div id='header'>
            <a class='left' href='/' id='logo'>
              <svg id='nb-logo' viewbox='0 0 44.1 51' xmlns='http://www.w3.org/2000/svg'>
                <polygon points='44.1 38.2 22 51 0 38.2 0 12.8 22 0 44.1 12.8 44.1 38.2' style='fill:#51c8ff'></polygon>
                <polygon points='12.5 36.4 25.3 49.1 44.1 38.2 44.1 27.2 31.2 14.2 22 14.2 22 30.2 12.5 36.4' style='fill:#1eb6fc'></polygon>
                <g id='logo-horizontal'>
                  <polygon points='31.2 35.1 21.9 39.9 12.5 35.1 21.7 30.3 31.2 35.1' style='fill:#fff'></polygon>
                  <polygon points='27.2 33.1 21.8 35.9 16.3 33.1 21.7 30.3 27.2 33.1' style='fill:#d3e5f0'></polygon>
                  <polygon points='21.9 39.9 31.2 35.1 31.2 36.5 21.9 41.2 21.9 39.9' style='fill:#c4d5df'></polygon>
                  <polygon points='21.9 39.9 12.5 35.1 12.5 36.4 21.9 41.2 21.9 39.9' style='fill:#cadce6'></polygon>
                  <polygon points='22 32.2 29.6 28.3 29.6 29.5 22 33.3 22 32.2' style='fill:#c4d5df'></polygon>
                  <polygon points='22 32.2 14.5 28.3 14.5 29.5 22 33.3 22 32.2' style='fill:#cadce6'></polygon>
                  <polygon points='29.6 28.3 22 32.2 14.5 28.3 22 24.5 29.6 28.3' style='fill:#fff'></polygon>
                  <polygon points='27.6 27.3 22 30.2 16.5 27.3 22 24.5 27.6 27.3' style='fill:#d3e5f0'></polygon>
                  <polygon points='22 26.2 31.2 21.4 31.2 22.9 22 27.5 22 26.2' style='fill:#c4d5df'></polygon>
                  <polygon points='22 26.2 12.9 21.4 12.9 22.9 22 27.5 22 26.2' style='fill:#cadce6'></polygon>
                  <polygon points='31.2 21.4 22 26.2 12.9 21.4 22 16.8 31.2 21.4' style='fill:#fff'></polygon>
                  <polygon points='28.8 20.2 22 23.7 15.3 20.2 22 16.8 28.8 20.2' style='fill:#d3e5f0'></polygon>
                  <polygon points='31.2 14.2 21.9 19 12.7 14.2 21.9 9.5 31.2 14.2' style='fill:#fff'></polygon>
                  <polygon points='21.9 19 31.2 14.2 31.2 15.7 21.9 20.4 21.9 19' style='fill:#c4d5df'></polygon>
                  <polygon points='21.9 19 12.7 14.2 12.7 15.7 21.9 20.4 21.9 19' style='fill:#cadce6'></polygon>
                </g>
              </svg>
              <span id='nb-title'>Nanobox</span>
            </a>
            <ul class='links'>
              <li><a href="https://dashboard.nanobox.io">Sign Up</a></li>
            </ul>
          </div>
            <div class='content-flex'>
                <div id='navigation'></div>
                <div id="content">
                    <div id='doc-content'>
@markdown
# Nanobox Cloud Provider -- Proxmox
Launch your Nanobox app on a Proxmox host.

There are a few steps you'll need to take to be able to use a Proxmox server to
host your Nanobox apps.  We'll try to summarize them all below.

- Your provider endpoint should be `{{ url('/api/v1/') }}`

- The credentials and basic data requested at this point will help determine
  where your app(s) will be deployed, and what capabilities you'll have to
  manage it/them. The `root@pam` account will work best, but shouldn't be
  required.

- On your server, you'll need two KVM backup images:

  - `vzdump-qemu-nanobox-ubuntu-40GB.vma.gz`
  - `vzdump-qemu-nanobox-ubuntu-250GB.vma.gz`

- In addition to the setup required by Nanobox (listed in the
  [Nanobox Docs](https://docs.nanobox.io/providers/create/)), also install the
  QEMU Guest Agent package (`apt-get install qemu-guest-agent`) and enable the
  QEMU Guest Agent serial device from the VM's Options tab.  The easiest way to
  create these is to create two VMs, one with a 40GB HDD and one with 250GB,
  install and configure Ubuntu as recommended above, create regular backups
  using the web UI or `vzdump`, and then rename them on the host (using SSH, or
  the Web UI's VNC/Spice shell).

- That should be it!  If you have any questions, contact [Dan Hunsaker](mailto:danhunsaker@gmail.com).
@endmarkdown
                    <div id='feedback'>
                        <div class='options'>
                            <a class='no' data-mp-path='{{ url()->current() }}' data-mp-response='no' data-mp-title='Proxmox Cloud Provider'>No</a>
                            <a class='yes' data-mp-path='{{ url()->current() }}' data-mp-response='yes' data-mp-title='Proxmox Cloud Provider'>Yes</a>
                        </div>
                        <p id='reach-out'>Reach out to <a href="mailto:danhunsaker@gmail.com">danhunsaker@gmail.com</a> and we'll try to help.</p>
                    </div>
                </div>
                <div id='footer'>
                    <ul id='links'>
                        <li>
                            <a href='https://nanobox.io/download'>Download</a>
                        </li>
                        <li>
                            <a href='https://guides.nanobox.io'>Guides</a>
                        </li>
                        <li>
                            <a href='https://nanoboxio.slack.com/messages/general/' target='_blank'>Nanobox on Slack</a>
                        </li>
                    </ul>
                    <p>Daniel Hunsaker (content) and Pagoda Box Inc. (design)  © Copyright 2017</p>
                </div>
            </div>
        </div>
    </body>
</html>
