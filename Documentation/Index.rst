========================
Media for TYPO3 CMS
========================

Media is the successor of DAM for TYPO3 CMS 6.0 and is logically built on top of FAL. FAL, for those who are unfamiliar, is a the File Abstraction Layer introduced in TYPO3 6.0enables to handle files in centralized way across the CMS. The basic idea of FAL is that every file has an entry in the database leverage the use of an asset.

Likewise DAM, Media is a tool for organizing contents and retrieving them by categories, mime types etc. Metadata can be inserted by a User or extracted automatically upon upload. Basically, Media provides the following set of features:

* Advance metadata support
* API for querying Image, Text, Audio, Video, Application from their repository
* Multi language handling of metadata
* File permission management
* File optimization on upload
* Mass upload of files
* Automatic Metadata extraction provided by EXT:metadata
* Integration in the text editor (RTE)


Project info and releases
=============================

The home page of the project is at http://forge.typo3.org/projects/extension-media/

Stable version:
http://typo3.org/extensions/repository/view/media

Development version:
https://git.typo3.org/TYPO3v4/Extensions/media.git

git clone git://git.typo3.org/TYPO3v4/Extensions/media.git

Live website with pre-configured extension:
http://get.typo3.org/bootstrap

Flash news about latest development:
http://twitter.com/fudriot


Installation
=================

Download the source code either from the `Git repository`_ to get the latest branch or from the TER for the stable releases. Install the extension as normal in the Extension Manager.

.. _Git repository: https://git.typo3.org/TYPO3v4/Extensions/media.git

Configuration
=================

Configuration is mainly provided in the Extension Manager and is pretty much self-explanatory. Check possible options there.

* In the the Variant tab, you can configure possible mount points per file type. A mount point can be considered as a sub folder within the storage where the files are going to be stored. This is useful if one wants the file to be stored elsewhere than at the root of the storage.
