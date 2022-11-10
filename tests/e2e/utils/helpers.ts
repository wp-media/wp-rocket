import os from 'os';
import fs from 'fs/promises';

let home_dir: String, install_path: String;
home_dir = os.homedir();

switch(os.platform()) { 
    case 'linux': { 
       install_path = 'wp-env';
       break; 
    } 
    default: { 
       install_path = '.wp-env'; 
       break; 
    } 
} 

/**
 * 
 * @param err Error message.
 */
export async function log_error(err, prefix = ''){
    console.log(prefix != '' ? prefix + ' - ' : '', err);
}

/**
 * 
 * @param page Page Object.
 */
export const save_settings = async ( page ) => {
    // save settings
    await page.locator('#wpr-options-submit').click();
}

export const sleep = (ms:number) => new Promise(r => setTimeout(r, ms));

/**
 * 
 * @param file String File name.
 * @returns String Absolute path to give file from OS.
 */
const get_dir = async (file: String) => {
    let dir;
    dir = (await fs.readdir(home_dir + '/' + install_path, { withFileTypes: true })).filter(dirent => dirent.isDirectory())[0].name;
    dir = home_dir + '/' + install_path + '/' + dir + '/WordPress/' + file;

    return dir;
}

/**
 * 
 * @param file Path to file to be read.
 * @returns String File content.
 */
export const read_file = async (file) => {
    return await fs.readFile(await get_dir(file), 'utf8');
}

/**
 * 
 * @param file Path to file to be written.
 * @param data Data to be written to file.
 */
export const write_to_file = async (file: String, data: String) => {
    await fs.writeFile(await get_dir(file), data);
    await sleep(1000);
}

/**
 * 
 * @param file Path to file.
 * @returns bool.
 */
export const file_exist = async (file: String) => {
    try {
        await fs.access(await get_dir(file));
        return true;
    } catch {
        return false;
    }
}

/**
 * 
 * @returns bool.
 */
export const is_rocket_active = async () => {
    try {
        await fs.access(await get_dir('wp-content/wp-rocket-config/localhost.php'));
        return true;
    } catch {
        return false;
    }
}