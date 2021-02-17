#!/usr/bin/env python3
import json
import shutil
import urllib.request
import os
import subprocess
import glob

default_plugins = ['advanced', 'alphalist2', 'approve', 'backlinks', 'bez', 'bureaucracy', 'button', 'cellbg', 'discussion',
                   'dropfiles', 'edittable', 'filelisting', 'flowcharts', 'folded', 'fontcolor', 'googledrawing',
                   'icons', 'iframe', 'include', 'indexmenu', 'ireadit', 'move', 'nosidebar', 'notification',
                   'numberof', 'pagemod', 'pdfjs', 'smtp', 'sqlite', 'struct', 'structat', 'structcombolookup',
                   'structgroup', 'structgroupby', 'structinputstretch', 'structjoin', 'structnotification', 'structodt',
                   'structrowcolor', 'structstatus', 'subnumberlist', 'tablecalc', 'tablelayout',
                   'telleveryone', 'templatepagename', 'toctweak', 'translation', 'wrap']

manual_plugins = {'isowikitweaks': 'https://github.com/gkrid/dokuwiki-plugin-isowikitweaks/archive/master.zip',
                  'redirect': 'https://github.com/gkrid/dokuwiki-plugin-redirect/archive/master.zip',
                  'simplenavi': 'https://github.com/solewniczak/simplenavi/archive/skipns.zip'}

# if not os.path.isdir('./engine_config.local'):
#     shutil.copytree('./engine_config', './engine_config.local')

engines_dir = './'

# current_dir = sys.path[0]
# engines_dir = os.path.join(current_dir, "core_engines")
# engines_dir = global_config['core_engines']
archive_filepath = os.path.join(engines_dir, "dokuwiki-stable.tgz")
command = f"wget -P {engines_dir} https://download.dokuwiki.org/src/dokuwiki/dokuwiki-stable.tgz"
subprocess.run(command, shell=True)
dokuwiki_path = os.path.join(engines_dir, "dokuwiki-stable.tgz")

command = f"tar -ztf {dokuwiki_path}|head -n1"
proc = subprocess.run(command, shell=True, stdout=subprocess.PIPE)
dw_version = proc.stdout.decode("utf-8").strip().rstrip('/')
engine_filepath = os.path.join(engines_dir, dw_version)

command = f"tar -zxf {archive_filepath} -C {engines_dir} && rm {archive_filepath}"
subprocess.run(command, shell=True)

# latest_sym_link = os.path.join(engines_dir, "latest")
# command = f"rm {latest_sym_link} ; ln -s {engine_filename} {latest_sym_link}"
# subprocess.run(command, shell=True)


EXTENSION_REPOSITORY_API = 'http://www.dokuwiki.org/lib/plugins/pluginrepo/api.php'
all_extensions = json.load(urllib.request.urlopen(EXTENSION_REPOSITORY_API))
extensions_dict = {extension['plugin']: extension for extension in all_extensions}
extensions_dir = os.path.join(engine_filepath, 'lib/plugins')

# Check main dokuwiki versions
previous_engine_filepath = os.path.join(engines_dir, 'dokuwiki')
previous_engine_version_filepath = os.path.join(previous_engine_filepath, 'VERSION')

if os.path.isfile(previous_engine_version_filepath):
    with open(previous_engine_version_filepath, 'r') as file:
        prev_version = file.read().replace('\n', '')
    current_engine_version_filepath = os.path.join(engine_filepath, 'VERSION')
    with open(current_engine_version_filepath, 'r') as file:
        current_version = file.read().replace('\n', '')
    if prev_version == current_version:
        print(f"Engine version didn't change: {prev_version}")
    else:
        print(f"Upgrading engine from {prev_version} to {current_version}")
else:
    print("No previous engine found.")


def get_plugin_version(info_file):
    with open(info_file) as fp:
        for line in fp:
            key, value = line.strip().split(maxsplit=1)
            if key == 'date':
                return value

def get_plugin_db_version(plugin_path):
    db_version_path = os.path.join(plugin_path, 'db/latest.version')
    if os.path.isfile(db_version_path):
        with open(db_version_path, 'r') as file:
            version = file.read().strip()
            return version
    return False

def compare_plugins_versions(name):
    ''' Check if plugin version changed between releases.
    Additionaly check if database update for sqlite plugins. '''
    if not os.path.isfile(previous_engine_version_filepath):
        print('ok')
        return

    previous_extensions_dir = os.path.join(previous_engine_filepath, 'lib/plugins')
    new_plugin_filepath = os.path.join(extensions_dir, name)
    old_plugin_filepath = os.path.join(previous_extensions_dir, name)

    new_plugin_info = os.path.join(new_plugin_filepath, 'plugin.info.txt')
    old_plugin_info = os.path.join(old_plugin_filepath, 'plugin.info.txt')

    if not os.path.isfile(old_plugin_info):
        print('installing new plugin')
    else:
        new_plugin_version = get_plugin_version(new_plugin_info)
        old_plugin_version = get_plugin_version(old_plugin_info)
        if new_plugin_version != old_plugin_version:
            print(f'upgrading from {old_plugin_version} to {new_plugin_version}', end='')
            # check if plugin updates database
            new_plugin_db_version = get_plugin_db_version(new_plugin_filepath)
            old_plugin_db_version = get_plugin_db_version(old_plugin_filepath)
            if new_plugin_db_version != False and old_plugin_db_version != False:
                if new_plugin_db_version != old_plugin_db_version:
                    print(f". upgrading plugin's db from {old_plugin_db_version} to {new_plugin_db_version}")
                else:
                    print('. no db upgrades')
            else:
                print()
        else:
            print('no upgrades')

# download plugins
def download_plugin(name, download_url=''):
    if download_url == '':
        if not name in extensions_dict:
            print(f'unknown plugin {name}')
            return
        print(f'downloading plugin {name} ...', end=' ')
        extension = extensions_dict[name]
        download_url = extension['downloadurl']
    else:
        print(f'downloading plugin {name} from {download_url} ...', end=' ')

    extension_archive_filepath = os.path.join(extensions_dir, f'{name}-archive')
    command = f"wget -P {engines_dir} {download_url} -O {extension_archive_filepath}"
    subprocess.run(command, shell=True, stderr=subprocess.DEVNULL)

    command = f"file -ib {extension_archive_filepath}"
    proc = subprocess.run(command, shell=True, stdout=subprocess.PIPE)
    mime = proc.stdout.decode("utf-8").split(';')[0].strip()

    if mime == 'application/zip':
        command_get_content = 'zipinfo -1 {archive}'
        command_extract = 'unzip {archive} -d {destination}'
    elif mime == 'application/gzip':
        command_get_content = 'tar -ztf {archive}'
        command_extract = 'tar -zxf {archive} -C {destination}'
    else:
        print(f'unknown mime type: {mime}')
        command = f"rm {extension_archive_filepath}"
        subprocess.run(command, shell=True)
        return
    # get extension main folder
    command = command_get_content.format(archive=extension_archive_filepath) + " | head -n1"
    proc = subprocess.run(command, shell=True, stdout=subprocess.PIPE)
    extension_maindir_filename = proc.stdout.decode("utf-8").strip().rstrip('/')
    extension_maindir_filepath = os.path.join(extensions_dir, extension_maindir_filename)
    extension_newdir_filepath = os.path.join(extensions_dir, name)

    command = command_extract.format(archive=extension_archive_filepath, destination=extensions_dir)+\
              f" && mv {extension_maindir_filepath} {extension_newdir_filepath} && rm {extension_archive_filepath}"
    subprocess.run(command, shell=True, stdout=subprocess.DEVNULL)
    compare_plugins_versions(name)


for plugin in default_plugins:
    download_plugin(plugin)

for plugin, url in manual_plugins.items():
    download_plugin(plugin, url)

# fill with default configuration
engine_conf = os.path.join(engine_filepath, 'conf/')
command = f"cp default_conf/* {engine_conf}"
subprocess.run(command, shell=True)

command = f"cp .htaccess {engine_filepath}"
subprocess.run(command, shell=True)

command = f"rm -r {previous_engine_filepath} && mv {engine_filepath} {previous_engine_filepath}"
subprocess.run(command, shell=True)