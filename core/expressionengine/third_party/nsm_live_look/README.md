NSM Live Look
=======================================

Overview
--------

NSM Live Look is a [Multi-Site Manager][ee_msm] compatible[ ExpressionEngine extension][ee_cp_extensions_manager] that allows you to view the latest published revision of an entry in the ExpressionEngine [control panel][ee_cp]. When enabled, a Live Look tab is added to the CP publish form which displays the entry in a rendered template context.


Getting started
-------------

### Requirements

NSM Live Look requires ExpressioEngine 2.0PB1.

Technical requirements include:

* PHP5
* A modern browser: [Firefox][firefox], [Safari][safari], [Google Chrome][chrome] or IE8+

### Installation

1. [Download][download] the latest version of NSM Live Look
2. Extract the .zip file to your desktop
3. Rename the extracted folder to `nsm_live_look` and copy it to your `system/expressionengine/third_party/` directory
4. Move (or symlink) `system/expressionengine/third_party/nsm_live_look/themes/nsm_live_look` to the `themes` directory

### Activation

1. Open the [Extensions Mananger][ee2_extension_manager]
2. Enable the NSM Live Look extension
3. Configure the extension settings

### Configuration

NSM Live Look has the following extension settings which need to be entered separately for each Multi-Site Manager site.

Note: All configuration options are site specific. When a new site is created be sure to save the extension settings for the new site to avoid errors.

#### Channel customisation

For each channel, you can set a preview URL which will be used by NSM Live Look to create the preview. Here's an example preview URL:

    /blog/post/{entry_id}/{url_title}/

The preview url setting will have the following variables replaced with entry specific attributes allowing you to create highly customisable urls:

* `{url_title}`
* `{entry_id}`
* `{channel_id}`
* `{title}`
* `{author_id}`
* `{status}`
* `{entry_date_day}`
* `{entry_date_month}`
* `{entry_date_year}`
* `{dst_enabled}`
* `{comment_total}`
* `{username}`
* `{email}`
* `{screen_name}`

User guide
---------

When you have configured NSM Live Look for a channel, go to the publish page for that channel and select the NSM Live Look tab. You will be shown a live preview of the entry on your site.

Release Notes
------------

### Upgrading?

* Before upgrading back up your database and site first, you can never be too careful.
* Never upgrade a live site, you're asking for trouble regardless of the addon.
* After an upgrade if you are experiencing errors re-save the extension settings for each site in your ExpressionEngine install.

There are no specific upgrade notes for this version.

### Change log

#### 0.3.0

* Added Morphine styles
* Rewrote Javascript
* Modified folder structure

#### 0.2.0

* Rewrite for multiple preview urls

#### 0.1.0

* Initial release with docs

Support
-------

Technical support is available primarily through the [ExpressionEngine forums][ee_forums]. [Leevi Graham][lg] and [Newism][newism] do not provide direct phone support. No representations or guarantees are made regarding the response time in which support questions are answered.

Although we are actively developing this addon, [Leevi Graham][lg] and [Newism][newism] makes no guarantees that it will be upgraded within any specific timeframe.

License
------

Ownership of this software always remains property of the author.

You may:

* Modify the software for your own projects
* Use the software on personal and commercial projects

You may not:

* Resell or redistribute the software in any form or manner without permission of the author
* Remove the license / copyright / author credits

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

[lg]: http://leevigraham.com

[nsm]: http://newism.com.au
[nsm_publish_plus]: http://leevigraham.com/cms-customisation/expressionengine/nsm-publish-plus/


[ee]: http://expressionengine.com/index.php?affiliate=newism
[ee_forums]: http://expressionengine.com/index.php?affiliate=newism&page=forums
[ee_cp]: http://expressionengine.com/index.php?affiliate=newism&page=docs/cp/index.html
[ee_cp_edit]: http://expressionengine.com/index.php?affiliate=newism&page=docs/cp/edit/index.html
[ee_cp_extensions_manager]: http://expressionengine.com/index.php?affiliate=newism&page=docs/cp/admin/utilities/extension_manager.html
[ee_msm]: http://expressionengine.com/index.php?affiliate=newism&page=downloads/details/multiple_site_manager/

[firefox]: http://firefox.com
[safari]: http://www.apple.com/safari/download/
[chrome]: http://www.google.com/chrome/

[lg_addon_updater]: http://leevigraham.com/cms-customisation/expressionengine/lg-addon-updater/
[gh_morphine_theme]: http://github.com/newism/nsm.morphine.theme

[ee2_module_manager]: http://expressionengine.com/public_beta/docs/cp/add-ons/module_manager.html
[ee2_extension_manager]: http://expressionengine.com/public_beta/docs/cp/add-ons/extension_manager.html

[download]: http://github.com/newism/nsm.live_look.ee_addon/zipball/master
[newism]: http://www.newism.com.au