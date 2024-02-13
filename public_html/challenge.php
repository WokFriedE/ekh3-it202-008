<!DOCTYPE html>
<html lang="en-US">

<head>
    <title>Problem 1</title>
    <meta charset="utf-8" />
    <script>
        window.addEventListener("load", () => {
            console.log("loaded via javascript");
            //TODO: add any extra onload processing you may need here
            // Ethan Ho - ekh3 - due 2/12/23 - modified 2/12/23
            getCurrentSelection()
        });

        function getCurrentSelection() {
            setTimeout(() => {
                //added this delay here as some solutions may require it to update properly (i.e., click code may complete before the navigation changes)
                //TODO: add code for processing the current selection 
                //Note: likely you'll want to call updateCurrentPage towards the end
                window.onhashchange = function() {
                    let currPage = window.location.hash.slice(1);
                    updateCurrentPage(currPage); // provides change to both <h1> and <title>
                    // document.getElementsByTagName("title")[0].innerText = currPage; // for my reference in the future
                }
            }, 100);
        }
    </script>
    <style>
        /* TODO: make edits here */
        nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background-color: #343E3D;
        }

        nav li a {
            float: left;
            padding: 10px;
            text-decoration: none;
            color: #FFFFFF;
            font-size: large;
            text-align: center;
        }

        nav a:hover {
            background-color: #333333;
        }

        ul li ul li {
            list-style-type: "✓";
            font-size: medium;
        }

        h1::first-letter,
        a::first-letter {
            text-transform: uppercase;
        }

        /* Updated to make it look better*/
        body {
            background-color: #E9EDDE;
        }

        ul li {
            font-family: Georgia, 'Times New Roman', Times, serif;
            font-size: large;
        }
    </style>
    <!-- make the necessary edits above this line -->


    <!-- Do not edit anything below this line, doing so will lose points-->
    <script src="https://it202-spring-22.herokuapp.com/M3/problem1.js">
        //Don't edit anything in this tag and do not delete it
    </script>
</head>
<!-- Do not edit anything below this line, doing so will lose points-->

<body onload="check();updateCurrentPage('start');">
    <header>
        <h2>Problem 1</h2>
    </header>
    <nav>
        <ul>
            <li><a href="#login">login</a></li>
            <li><a href="#register">register</a></li>
            <li><a href="#profile">profile</a></li>
            <li><a href="#logout">logout</a></li>
        </ul>
    </nav>
    <h1></h1>
    <h3>Challenges</h3>
    <ul>
        <li>Edit the given <code>&lt;style&gt;</code> tag to customize the appearance of this page
            <ul>
                <li>(1 pt) Make the navigation horizontal</li>
                <li>(1 pt) Get rid of the navigation list item markers</li>
                <li>(1 pt) Give the navigation a background</li>
                <li>(1 pt) Make the links (or their surrounding area) change color on mouseover</li>
                <li>(1 pt) Change the "bullet points" of this entire list to checkmarks (✓)</li>
                <li>(1 pt) Use CSS to uppercase the first character of the content in the <code>&lt;h1&gt;</code> tag
                    and the
                    <code>&lt;a&gt;</code> tags
                </li>
                <li>(1 pt) Add some styling of your choice (that doesn't conflict with any requests in this assignment),
                    this will be mentioned in the submission</li>
            </ul>
        </li>
        <li>(1 pt) Any style applied to unordered list in the <code>&lt;nav&gt;</code> tag should not apply to this list
            (i.e., nav
            and this
            list should
            not appear identical)</li>
        <li>
            Any time a navigation link is clicked have it do the following:
            <ul>
                <li>(1 pt) Update the content of the <code>&lt;h1&gt;</code> tag with the link text</li>
                <li>(1 pt) Update the content of the <code>&lt;title&gt;</code> tag with the link text</li>
                <li>Hint: Get the word of the current navigation and pass it to updateCurrentPage()</li>
            </ul>
        </li>
    </ul>
    <footer></footer>
</body>

</html>