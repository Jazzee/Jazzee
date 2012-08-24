;PHP ini file for Jazzee Configuration
;Many of these items are optional and have been commented out
;In places where a default is usefull it is selected
;details on each option can be found at http://docs.jazzee.org/en/latest/configuration.html


;### DATABASE CONNECTION ###;
;#specifics and allowed options at http://www.doctrine-project.org/docs/dbal/2.0/en/reference/configuration.html
dbHost=
;dbPort=
dbName=
dbUser=
dbPassword=
dbDriver=
dbCharset=

;#The FROM email address to apply by default and to messages without a sender (like logs)
;#if blank mail server will probably apply a default based on your hostname
;#if you speicify an address you can also specify a nice name like 'Jazzee System'
;mailDefaultFromAddress=
;mailDefaultFromName=

;### MISC OPTIONS ###;
;#Recaptch keys are free - go to http://www.google.com/recaptcha
;recaptchaPrivateKey=
;recaptchaPublicKey=

;PDFlib License Key - purchase at http://www.pdflib.com/
;There is an education discount
;PDF isn't required for PDFs just some advanced functions like generating a combined PDF of the entire application
;pdflibLicenseKey=