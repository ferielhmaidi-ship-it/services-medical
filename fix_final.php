<?php

$files = [
    [
        'path' => __DIR__.'/src/Command/TestLoginCommand.php',
        'search' => "    public function __construct(EntityManagerInterface \$entityManager, UserPasswordHasherInterface \$passwordHasher)
    {
        parent::__construct();
        \$this->entityManager = \$entityManager;
        \$this->passwordHasher = \$passwordHasher;
    }",
        'replace' => "    public function __construct(EntityManagerInterface \$entityManager)
    {
        parent::__construct();
        \$this->entityManager = \$entityManager;
    }"
    ],
    [
        'path' => __DIR__.'/src/Controller/DocumentPdfController.php',
        'search' => '        $html .= \'<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Nom</th><td style="border:1px solid #333;padding:5px;">\' . htmlspecialchars($document->getLastName()) . \'</td></tr>\';',
        'replace' => '        $html .= \'<tr><th style="text-align:left;border:1px solid #333;padding:5px;">Nom</th><td style="border:1px solid #333;padding:5px;">\' . htmlspecialchars((string) $document->getNom()) . \'</td></tr>\';'
    ],
    [
        'path' => __DIR__.'/src/Controller/PatientDashboardController.php',
        'search' => "        \$doctorGroups = array_values(\$filteredGroups);",
        'replace' => "        \$doctorGroups = \$filteredGroups;"
    ]
];

foreach (\$files as \$file) {
    if (file_exists(\$file['path'])) {
        \$content = file_get_contents(\$file['path']);
        \$newContent = str_replace(\$file['search'], \$file['replace'], \$content);
        if (\$content !== \$newContent) {
            file_put_contents(\$file['path'], \$newContent);
            echo "Fixed " . basename(\$file['path']) . "\n";
        } else {
            echo "No changes made to " . basename(\$file['path']) . " (string not found)\n";
        }
    } else {
        echo "File not found: " . \$file['path'] . "\n";
    }
}
