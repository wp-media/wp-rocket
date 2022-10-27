import os from 'os';
import fs from 'fs/promises';

let home_dir = os.homedir();

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

/**
 * 
 * @param file String File name.
 * @returns String Absolute path to give file from OS.
 */
const get_dir = async (file: String) => {
    let dir = (await fs.readdir(home_dir + '/.wp-env', { withFileTypes: true })).filter(dirent => dirent.isDirectory())[0].name;
    return home_dir + '/.wp-env/' + dir + '/WordPress/' + file;
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