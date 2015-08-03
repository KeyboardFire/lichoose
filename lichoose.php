<?php
    // lichoose.php
    // ============
    // author: Keyboard Fire <andy@keyboardfire.com>
    // date: Oct 02, 2015
    // license: MIT
    // live at: http://keyboardfire.com/lichoose/

    if (isset($_POST['spin'])) {
        $fh = fopen('./lichoose.txt', 'r');
        $time = (int) fgets($fh);
        if ($time > time()) {
            header('Refresh: 0');
            die();
        }
        fclose($fh);

        $fh = fopen('./lichoose.txt', 'w');
        $time = time() + 15;
        $rand = mt_rand(0, 6);
        fwrite($fh, $time . "\n" . $rand . "\n");
        fclose($fh);

        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    } else {
        $fh = fopen('./lichoose.txt', 'r');
        $time = (int) fgets($fh);
        $rand = (int) fgets($fh);
        fclose($fh);
    }
?><!DOCTYPE html>
<html lang='en'>
    <head>
        <meta charset='utf-8' />
        <title>Lichoose</title>
        <script src='http://code.jquery.com/jquery-1.11.3.min.js'></script>
        <script src='http://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js'></script>
        <style>
            @font-face {
                font-family: "lichess";
                src: url("http://lichess1.org/assets/font44/fonts/lichess.eot");
                src: url("http://lichess1.org/assets/font44/fonts/lichess.eot?#iefix") format("embedded-opentype"),
                    url("http://lichess1.org/assets/font44/fonts/lichess.woff") format("woff"),
                    url("http://lichess1.org/assets/font44/fonts/lichess.ttf") format("truetype"),
                    url("http://lichess1.org/assets/font44/fonts/lichess.svg#lichess") format("svg");
                font-weight: normal;
                font-style: normal;
            }
            span.icon { font-family: 'lichess'; }

            div#msg {
                float: left;
                margin-right: 4px;
            }

            ul#variants {
                height: 24px;
                overflow: hidden;
                float: left;
                padding: 0;
                margin: 0;
            }

            ul#variants li {
                height: 24px;
                list-style: none;
            }

            #spin span#time {
                font-style: italic;
            }
        </style>
        <script>
            // most code stolen from http://jsfiddle.net/Ldvwp8uv/1/light/

            var vs, nDup = 8, stopPanning = false;

            function spin() {
                stopPanning = true;
                var animCount = <?php echo($rand); ?> + vs.length * (nDup - 1);
                vs.first().stop().animate({ marginTop: 0 }, 200, 'swing', function() {
                    vs.first().animate({
                        marginTop: -(vs.first().outerHeight(true) * animCount)
                    }, 5000, 'easeOutQuad', checkFunc);
                });
            }

            $(function() {
                vs = $('ul#variants li');
                for (var i = 0; i < nDup; ++i) {
                    vs.clone().appendTo('ul#variants');
                }

                // slowly pan while not spinning
                var panFunc = function() {
                    if (stopPanning) return;
                    vs.first().css('margin-top', '0px');
                    vs.first().animate({
                        marginTop: -(vs.first().outerHeight(true) * vs.length)
                    }, 7500, 'linear', panFunc);
                };
                panFunc();
            });

            function checkFunc() {
                $.ajax({
                    url: 'lichoose.txt',
                    success: function(data) {
                        if ((+data.split('\n')[0]) > ((+new Date) / 1000)) {
                            location.reload();
                        } else {
                            setTimeout(checkFunc, 1000);
                        }
                    },
                    cache: false
                });
            }
        </script>
    </head>
    <body>
        <div id='msg'>You're playing...</div>
        <ul id='variants'>
            <li><span class='icon'>+</span> Standard</li>
            <li><span class='icon'>'</span> Chess960</li>
            <li><span class='icon'>(</span> King of the Hill</li>
            <li><span class='icon'>.</span> Three-check</li>
            <li><span class='icon'>@</span> Antichess</li>
            <li><span class='icon'>&gt;</span> Atomic</li>
            <li><span class='icon'>_</span> Horde</li>
        </ul>
        <div style='clear: both; height: 10px'></div>
        <?php if ($time > time()) { ?>
            <p id='spin' style='font-size: 12px; color: grey'>
                Next spin in <span id='time' style='font-style: italic'>[loading...]</span> seconds
            </p>
            <script>
                var time = <?php echo($time); ?>;
                var intr = setInterval(function() {
                    var waitTime = time - Math.floor((+new Date) / 1000);
                    if (waitTime <= 0) {
                        spin();
                        $('#spin').text('Hold on!');
                        clearInterval(intr);
                    } else {
                        $('#time').text(waitTime);
                    }
                }, 200);
            </script>
        <?php } else { ?>
            <form id='spin' action='' method='POST'>
                <button name='spin' type='submit'>Spin the wheel!</button>
            </form>
            <script> checkFunc(); </script>
        <?php } ?>
    </body>
</html>
