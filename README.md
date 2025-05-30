<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
</p>

## Built on Laravel
App that record VOIP records from a MITEL Voip server

## Linux service install 
in /etc/systemd/system/voip-logger.service place the following code

[Unit]
Description=VOIPLogger TCP Listener
After=network.target

[Service]
ExecStart=/usr/bin/php {location of app}/artisan tcp:listen
WorkingDirectory={location of app}
Restart=always
RestartSec=5
User=www-data
Environment=APP_ENV=production
Environment=LOG_CHANNEL=stack

[Install]
WantedBy=multi-user.target

# reload systemd and start service
sudo systemctl daemon-reexec
sudo systemctl daemon-reload
sudo systemctl enable laravel-tcp
sudo systemctl start laravel-tcp
sudo systemctl status laravel-tcp