<?php 
// <!-- delete job from given job_id -->
$job_id = $_POST['job_id'];
$delete_job = "DELETE FROM jobs WHERE job_id = '$job_id'";
$delete_job_result = mysqli_query($conn, $delete_job);
if($delete_job_result){
    echo "Job deleted successfully";
}else{
    echo "Error deleting job";
}



