<h1>YubiKey Plugin</h1>
Authenticate a user with a Yubikey USB device.
<h2>What's this all about?</h2>
<p>The YubiKey USB-key delivers a one-time passcode (OTP) with
a simple touch of a button. No SMS-like passcodes to retype 
from one device to another. The YubiKey identifies itself as 
an external keyboard, which eliminates the need for client 
software or drivers. The Key is designed to ensure it will 
never be a vector for viruses or malware.</p>
<p>Any computer which can use a USB keyboard can also use the 
YubiKey, regardless of the computer hardware, operating system
or system drivers. The YubiKey AES Key information can never 
be extracted from a YubiKey device. Further, only the YubiKey 
security related codes are directly read from the YubiKey 
when in use.</p>
<p>This plugin makes it possible to authenticate 
a user with pimcore by using a YubiKey.</p>

<h2>Installation</h2>
<p>Put the files of this plugin in the directory /plugins/YubiKey
and activate/install it in pimcore's plugins settings.</p>

<h2>Configuration</h2>
<p>The settings are located in pimcore's menu under <i>Settings/YubiKey Settings</i>.<br>
  You can set these parameters:
</p>  
<ul>
  <li>
    <strong>Use local authentification</strong><br>
    Check this box if you want to use the local component of the plugin.<br>
    This enables you to locally authenticate users with their YubiKey.
  </li>
  <li>
    <strong>Private Key / Public Key</strong><br>
    If you want to use the authentication of local users with a remote server,
    you'll need to configure a private and public key here.<br>
    You can either enter keys you created by yourself or backupped keys, or
    use the button <i>Create new key pair</i> to let the server create a new 
    pair for you.
  </li>
  <li>
    <strong>Use central authentification</strong>
    Check this box if you have a remote server to authenticate the users with.<br>
    You can obtain the remote server component from us at <a href="http://www.weblizards.de/">www.weblizards.de</a>.
  </li>
  <li>
    <strong>Identifier</strong>
    Enter an identifier here. You'll have to use this exact identifier on the remote
    server to connect your instance with the server.
  </li>
  <li>
    <strong>Server URL</strong>
    The URL of your server. This is just the hostname with the protocal, like <i>http://www.weblizards.de</i>
    without a path. The path is added by the plugin.
  </li>
  <li>
    <strong>Public Key</strong>
    The public key of the server. You'll get the key in the settings of the remote server component.
  </li>
</ul>

<h2>Usage</h2>
<ul>
  <li>Open the settings of a user and click the <i>YubiKey settings</i> tab.</li>
  <li>Check the <i>Active</i> checkbox if necessary.</li>
  <li>Click the <i>Add</i> button and enter the serial number of the user's YubiKey.
      You can optionally enter a comment. To enter the serial number you simply can
      use the users YubiKey - only the serial number of the created One Time Pad will
      be used.
  </li>
  <li>Instead of entering the users password you can now simply press the button
    on the yubikey.
  </li>
</ul>

<h2>Taking it further</h2>
<p>
  The Plugin can communicate with a remote server component to authenticate a user.
  With this, you can administer your installations, the users that may log into
  an installation and their corresponding keys in a central place.<br>
  Please contact us at <a href="mailto:info@weblizards.de">info@weblizards.de</a> if
  you need further information!
</p>

<h2>Dependencies</h2>
<p>
This plugin needs the curl extension to communicate with the
servers of yubico.com</p>