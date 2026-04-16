<?php

function fixFile($path, $search, $replace)
{
    if (!file_exists($path)) {
        echo "File not found: $path\n";
        return;
    }
    $content = file_get_contents($path);
    $newContent = str_replace($search, $replace, $content);
    if ($content !== $newContent) {
        file_put_contents($path, $newContent);
        echo "Fixed $path\n";
    }
    else {
        echo "No change for $path (search string not found)\n";
    }
}

// Fix 1: TestLoginCommand.php
fixFile(
    'src/Command/TestLoginCommand.php',
    'public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }',
    'public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }'
);

// Fix 2: DocumentPdfController.php
fixFile(
    'src/Controller/DocumentPdfController.php',
    '\' . htmlspecialchars($document->getLastName()) . \'',
    '\' . htmlspecialchars((string) $document->getNom()) . \''
);

// Fix 3: PatientDashboardController.php
fixFile(
    'src/Controller/PatientDashboardController.php',
    '$doctorGroups = array_values($filteredGroups);',
    '$doctorGroups = $filteredGroups;'
);
