/**
 * 
 * @param err Error message.
 */
export async function log_error(err, prefix = ''){
    console.log(prefix != '' ? prefix + ' - ' : '', err);
}

export const save_settings = async ( page ) => {
    // save settings
    await page.locator('#wpr-options-submit').click();
}