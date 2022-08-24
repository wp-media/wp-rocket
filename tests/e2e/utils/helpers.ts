
/**
 * 
 * @param err Error message.
 */
export async function log_error(err, prefix = ''){
    console.log(prefix != '' ? prefix + ' - ' : '', err);
}
