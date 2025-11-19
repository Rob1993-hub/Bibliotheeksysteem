<?php
// ========= Globals =========
$authors = ["Blake Crouch", "Andy Weir", "Phil K. Dick"];

$books = [
    [
        "title" => "Dark Matter",
        "author" => "Blake Crouch",
        "isbn" => "978-1101904220",
        "publisher" => "Ballantine Books",
        "publication_date" => "2016-06-26",
        "pages" => 320
    ],
    [
        "title" => "Project Hail Mary",
        "author" => "Andy Weir",
        "isbn" => "978-0-593-39556-1",
        "publisher" => "Ballantine Books",
        "publication_date" => "2021-05-04",
        "pages" => 496
    ],
    [
        "title" => "Do Androids Dream of Electric Sheep",
        "author" => "Phil K. Dick",
        "isbn" => "978-1780220383",
        "publisher" => "Gollancz",
        "publication_date" => "1968-02-24",
        "pages" => 876
    ],
];

// ========= Helpers =========
function displayBookList(array $list): void
{
    if (count($list) === 0) {
        echo "The list is empty.\n\n";
        return;
    }
    foreach ($list as $index => $book) {
        echo ($index + 1) . ") " . $book["title"] . " — " . $book["author"] .
            " (ISBN: " . $book["isbn"] . ")\n";
    }
    echo "\n";
}

function showAuthorsMenu(): string
{
    global $authors;

    if (count($authors) === 0) {
        echo "The author list is empty.\n";
        return "";
    }
    echo "=== Authors ===\n";
    foreach ($authors as $index => $author) {
        echo ($index + 1) . ") " . $author . "\n";
    }
    echo "\n";

    while (true) {
        $choice = (int) readline("Kies een auteur [1-" . count($authors) . "]: ");
        if ($choice >= 1 && $choice <= count($authors)) {
            $selectedAuthor = $authors[$choice - 1];
            echo "Je koos: $selectedAuthor\n";
            return $selectedAuthor;
        }
        echo "Ongeldige keuze, probeer opnieuw.\n";
    }
}

function getBookDetails(string $author): array
{
    // Titel
    while (true) {
        $title = trim(readline("Titel: "));
        if ($title === "") {
            echo "Title can't be empty.\n";
        } else {
            break;
        }
    }

    // ISBN
    while (true) {
        $isbn = trim(readline("ISBN: "));
        if ($isbn === "") {
            echo "ISBN can't be empty.\n";
        } else {
            break;
        }
    }

    // Uitgever
    while (true) {
        $publisher = trim(readline("Publisher: "));
        if ($publisher === "") {
            echo "Publisher can't be empty.\n";
        } else {
            break;
        }
    }

    // Publicatiedatum (YYYY-MM-DD)
    while (true) {
        $publication_date = trim(readline("Publication date (YYYY-MM-DD): "));
        $dt = DateTime::createFromFormat('Y-m-d', $publication_date);
        if (!$dt || $dt->format('Y-m-d') !== $publication_date) {
            echo "Please use the format YYYY-MM-DD (e.g. 2021-05-04).\n";
        } else {
            break;
        }
    }

    // Aantal pagina's (positief geheel getal)
    while (true) {
        $pagesInput = trim(readline("Aantal pagina's: "));
        if ($pagesInput === "" || !ctype_digit($pagesInput)) {
            echo "Voer een positief geheel getal in.\n";
            continue;
        }
        $pages = (int) $pagesInput;
        if ($pages <= 0) {
            echo "Aantal pagina's moet groter zijn dan 0.\n";
        } else {
            break;
        }
    }

    return [
        "title" => $title,
        "author" => $author,
        "isbn" => $isbn,
        "publisher" => $publisher,
        "publication_date" => $publication_date,
        "pages" => $pages
    ];
}


// Normaliseer ISBN zodat spaties/streepjes/hoofdletters geen valse duplicates geven
function normalizeIsbn(string $raw): string
{
    $s = trim($raw);
    $s = strtolower($s);
    $s = str_replace([' ', '-'], '', $s);
    return $s;
}

function isbnExists(string $isbn): bool
{
    global $books;
    $needle = normalizeIsbn($isbn);
    $all   = array_map('normalizeIsbn', array_column($books, 'isbn'));
    return in_array($needle, $all, true);
}

function addBook(array $book): void
{
    global $books;
    $books[] = $book;
    echo "Boek toegevoegd: {$book['title']} van {$book['author']}.\n\n";
}

// ========= Handlers =========
function handleAddBook(): void
{
    $author = showAuthorsMenu();
    if ($author === "") return;

    $book = getBookDetails($author);

    // Duplicate-ISBN check
    if (isbnExists($book['isbn'])) {
        echo "ISBN already exists. Book was not added.\n\n";
        return;
    }

    addBook($book);
}

function showAllBooks(): void
{
    global $books;
    echo "=== All Books ===\n";
    displayBookList($books);
}

function handleRemoveBook(): void
{
    global $books;

    echo "=== Remove Book ===\n";
    if (count($books) === 0) {
        echo "No books to remove.\n\n";
        return;
    }
    displayBookList($books);

    while (true) {
        $choice = (int) readline("Kies het nummer van het boek om te verwijderen [1-" . count($books) . "]: ");
        if ($choice >= 1 && $choice <= count($books)) {
            $removed = $books[$choice - 1];
            array_splice($books, $choice - 1, 1);
            echo "Verwijderd: {$removed['title']} van {$removed['author']}.\n\n";
            return;
        }
        echo "Ongeldige keuze, probeer opnieuw.\n";
    }
}

function handleSearchByAuthor(): void
{
    global $books;

    echo "=== Books by Author ===\n";
    $author = showAuthorsMenu();
    if ($author === "") return;

    $filtered = array_values(array_filter($books, function ($b) use ($author) {
        return $b['author'] === $author;
    }));

    if (count($filtered) === 0) {
        echo "Geen boeken gevonden voor: $author\n\n";
        return;
    }
    displayBookList($filtered);
}

// ========= UI + Runner =========
function showMainMenu(): void
{
    echo "=== Personal Library ===\n";
    echo "1) Add Book\n";
    echo "2) View All Books\n";
    echo "3) Remove Book\n";
    echo "4) Books by Author\n";
    echo "5) Quit\n";
}

function run(): void
{
    while (true) {
        showMainMenu();
        $choice = (int) readline("Kies een optie: ");

        switch ($choice) {
            case 1:
                handleAddBook();
                break;
            case 2:
                showAllBooks();
                break;
            case 3:
                handleRemoveBook();
                break;
            case 4:
                handleSearchByAuthor();
                break;
            case 5:
                echo "Tot ziens!\n";
                exit;
            default:
                echo "Ongeldige keuze.\n\n";
        }
    }
}

run();
