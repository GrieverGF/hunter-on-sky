python3 adoc_galaxy.py >a.txt
asciidoctor -a allow-uri-read a.txt
asciidoctor-pdf -a allow-uri-read a.txt
cp a.html ../../misp-website/galaxy.html
cp a.pdf ../../misp-website/galaxy.pdf
scp -l 81920 a.html circl@cpab.circl.lu:/var/www/nwww.circl.lu/doc/misp-galaxy/index.html
scp -l 81920 a.pdf  circl@cpab.circl.lu:/var/www/nwww.circl.lu/doc/misp-galaxy/galaxy.pdf
