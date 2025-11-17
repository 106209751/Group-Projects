<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="styles/styles.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="images/Companylogo.png">
    <title>About Us</title>
</head>
<body>
    <div class="container">
        <style>
            .id{
                text-align: right;
            }
            table{
                align-items: center;
            }
            table tr th{
                font-weight: bold;
            }
            td:hover{
                background-color: lightblue;
            }
        </style>
        <?php include 'header.inc'; ?>
    
   <h1><strong>Welcome to the Group 3 page</strong></h1>
    <h2>Contact Information</h2>
   <ul> 
  <li> Members:</li>Thanh Sang Phuoc Le, Quang Anh Luu
  <li>Tutors:</li>  Mr.Binh, Mr,Thomas
   <li>Leader contact:</li> <a href="mailto:logmilo12@gmail.com">logmilo12@gmail.com</a>
        </ul>
        <h2>Group imformation</h2>
        <p> We are students of Data Science major at Swinbyrne University Vietnam. This is our first web developing project. We are very excited to learn and explore more about web developing. </p>
        <h2>Members Profile</h2>

        <table>
            <tr>
                <th>Name</th>
                <th>Student ID</th>
                <th>Interest</th>
                <th>Contribution</th>
            </tr>
            <tr>
                <td>
                    <h3>Thanh Sang Phuoc Le</h3>
                </td>
                <td class="id">SWH03150</td>
                <td>Web developing, Playing games</td>
                <td>Homepage and jobs page confirguration, CSS styles,EOIs table,Database works</td>
            </tr>
            <tr>
                <td>
                    <h3>Quang Anh Luu</h3>
                </td>
                <td class="id">SWH03105</td>
                <td>Web developing, Singing, Hanging out</td>
                <td>Apply page and about page confirguration, CSS styles,Creating management function,Enhancements for the website</td>
            </tr>
        </table>
    <?php include 'footer.inc'; ?>
    </div>
</body>
</html>
