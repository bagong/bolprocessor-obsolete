# Bol Processor
Bernard Bel and Anthony Kozar are working towards a new Bolprocessor version, a command-line app and a php-based web-interface. The previous version 2.9.8 is a Carbon based 32-bit application and cannot be run on Catalina any more. The console app currently runs on MacOS, Linux (incl. Raspbian) and Windows (MSYS2/MinGW build), and is targeted at becoming fully cross-platform. The development work is pushed to the SourceForge repo, this repo just mirrors the development at leisure as time goes by ;-)

## Project homepage

https://sourceforge.net/projects/bolprocessor/

The project page on Sourceforge provides direct to the documentation, the git repo, mailing-list links and a forum. Most public discussion happens on the development mailing list. Current development works towards a console binary and a php-based web-interface.

## Direct links

### bp git repo

https://git.code.sf.net/p/bolprocessor/git

Current development happens on the ANSI-branch.

### Documentation

http://bolprocessor.sourceforge.net/docs/

### bp-console/bp3 binary download and development files snapshot from Anthony Kozar

http://www.anthonykozar.net/files/BolProcessor/

### Various bp-related files from Bernard Bel including the php-frontend when a snapshot is current

https://leti.lt/bolprocessor/

### Mailing lists:

bolprocessor-announce@lists.sourceforge.net  
bolprocessor-devel@lists.sourceforge.net (most active)  
bp2-list@yahoogroups.com

## bp-console (bp 2.999...) and snapshots of development files

http://www.anthonykozar.net/files/BolProcessor/

## Current render chain
bp-grammar -gr.xyz | bpconsole -> csound-score xyz.sco | csound -> xyz.aiff

*Note* this repo only mirrors the SF-files. The additional branch "bagong" just merges the latest snapshot of Bernard's php-frontend into Anthony's work.

