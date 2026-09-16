$projectRoot = 'C:\wamp64\www\OQS_project'
$docxPath = Join-Path $projectRoot 'assignment\Group10_UI_Assignment.docx'
$tempRoot = Join-Path $projectRoot 'assignment\tmpdocx'

if (Test-Path $tempRoot) { Remove-Item $tempRoot -Recurse -Force }
$null = New-Item -ItemType Directory -Path (Join-Path $tempRoot 'word') -Force
$null = New-Item -ItemType Directory -Path (Join-Path $tempRoot '_rels') -Force
$null = New-Item -ItemType Directory -Path (Join-Path $tempRoot 'docProps') -Force

$styleXml = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/></w:style>
  <w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:qFormat/><w:rPr><w:b/><w:sz w:val="32"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:qFormat/><w:rPr><w:b/><w:sz w:val="28"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:qFormat/><w:rPr><w:b/><w:sz w:val="38"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="ListParagraph"><w:name w:val="List Paragraph"/><w:basedOn w:val="Normal"/><w:qFormat/></w:style>
</w:styles>
'@
Set-Content -Path (Join-Path $tempRoot 'word\styles.xml') -Value $styleXml -Encoding UTF8

$contentTypes = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>
'@
Set-Content -Path (Join-Path $tempRoot '[Content_Types].xml') -Value $contentTypes -Encoding UTF8

$rels = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
'@
Set-Content -Path (Join-Path $tempRoot '_rels\.rels') -Value $rels -Encoding UTF8

$core = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <dc:title>Group 10 – Online Examination Portal UI</dc:title>
  <dc:creator>ARIHIHIKWIZA OSCAR and OPIO LAMECK</dc:creator>
  <cp:lastModifiedBy>Copilot</cp:lastModifiedBy>
</cp:coreProperties>
'@
Set-Content -Path (Join-Path $tempRoot 'docProps\core.xml') -Value $core -Encoding UTF8

$app = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
  <Application>Microsoft Office Word</Application>
</Properties>
'@
Set-Content -Path (Join-Path $tempRoot 'docProps\app.xml') -Value $app -Encoding UTF8

function New-Paragraph($text, $style = $null) {
    if ($style) {
        return @"
<w:p>
  <w:pPr><w:pStyle w:val="$style"/></w:pPr>
  <w:r><w:t xml:space="preserve">$([System.Security.SecurityElement]::Escape($text))</w:t></w:r>
</w:p>
"@
    }

    return @"
<w:p>
  <w:r><w:t xml:space="preserve">$([System.Security.SecurityElement]::Escape($text))</w:t></w:r>
</w:p>
"@
}

function New-TableRow($cells) {
    $row = '<w:tr>'
    foreach ($cell in $cells) {
        $row += @"
      <w:tc><w:p><w:r><w:t xml:space="preserve">$([System.Security.SecurityElement]::Escape($cell))</w:t></w:r></w:p></w:tc>
"@
    }
    $row += '</w:tr>'
    return $row
}

$parts = @(
    New-Paragraph 'Group 10', 'Title',
    New-Paragraph 'Online Examination Portal UI', 'Title',
    New-Paragraph 'Prepared by:', 'Heading1',
    New-Paragraph 'ARIHIHIKWIZA OSCAR (Reg: 2024/U/MMU/BCS/00064)', 'ListParagraph',
    New-Paragraph 'OPIO LAMECK (Reg: 2024/U/MMU/BCS/00055)', 'ListParagraph',
    New-Paragraph 'Date: 16 September 2026', 'ListParagraph',
    New-Paragraph '1. Introduction and chosen system', 'Heading1',
    New-Paragraph 'The chosen real system is the KoKCS Online Assessment Portal at https://online-quiz-m8ru.onrender.com/, observed on 16 September 2026. The main task studied was a student logging in, opening the student dashboard, selecting an active quiz, and preparing to submit before the deadline. This system is closely related to the assignment category “Online CA / Examination Portal UI” because it is a real web-based assessment platform used for assessment management and exam submission.',
    New-Paragraph 'The interface is strongly aligned with the course theme because it contains a login flow, dashboard, quiz cards, timer, progress indicators, and action buttons. These elements make it a clear example of a web-based GUI used for educational assessment and student task completion.',
    New-Paragraph '2. Part A: Interface anatomy', 'Heading1',
    New-Paragraph 'Type of UI in use', 'Heading2',
    New-Paragraph 'The system is a web-based GUI, with form-based elements and direct manipulation. It fits this classification because it runs in a browser, uses input fields for email and password, and uses buttons, cards, and radio options to support interaction.',
    New-Paragraph 'Dominant interface metaphors', 'Heading2',
    New-Paragraph 'The dominant metaphors are the dashboard, the assessment desk, and the progress bar. The dashboard metaphor helps students understand where to find available quizzes and see task status. The assessment desk metaphor helps the user understand the flow of exam-taking. The progress bar helps the user feel the exam is moving forward. The metaphor helps by reducing confusion, but it breaks slightly when the interface feels more like a general app dashboard than a strict exam interface.',
    New-Paragraph 'Dominant interaction styles', 'Heading2',
    New-Paragraph 'The main interaction styles are form fill-in, direct manipulation, and menu-based navigation. Students enter credentials, click quiz cards, and select answers. This is a strong fit for the task because examination portals are usually best handled through form-based and direct-selection interactions.',
    New-Paragraph 'Affordances and feedback', 'Heading2',
    New-Paragraph 'Affordance 1: the Start Quiz button clearly suggests that clicking it begins the assessment. Affordance 2: the answer radio buttons clearly suggest that options can be selected. Feedback 1: the timer shows remaining time and indicates the quiz is active. Feedback 2: the progress bar gives users visible progress awareness. Missing feedback: the system does not strongly confirm whether the answer has been saved before submission, and the final submission step could be more explicit.',
    New-Paragraph 'Gulf of Execution and Gulf of Evaluation', 'Heading2',
    New-Paragraph 'Gulf of Execution: a student may not immediately know how to move from the dashboard to the quiz and then to submission. To shrink this gulf, the interface should display a clearer “Start quiz now” call-to-action and a short task summary before the quiz begins. Gulf of Evaluation: the user may not clearly know whether the selected answer has been recorded and whether the quiz is still active. To shrink this gulf, the interface should show “Answer saved” feedback and stronger status text after each selection.',
    New-Paragraph 'Miller, Hick, and Fitts observations', 'Heading2',
    New-Paragraph 'Miller’s Law: the system reduces cognitive load by presenting one question at a time rather than all questions at once. Hick’s Law: the limited number of visible choices makes decision time shorter because the learner is not overloaded with too many options. Fitts’s Law: the large “Start Quiz” and answer buttons are easy to click, which improves efficiency and reduces accidental misses.',
    New-Paragraph '3. Part B: Usability evaluation', 'Heading1',
    New-Paragraph 'B1. Eight usability attributes', 'Heading2',
    New-Paragraph 'The following scores are based on the portal’s live interface and the observed student assessment flow.',
    @"
<w:tbl>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Attribute</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Score</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Evidence</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Clear</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>4/5</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>The login, dashboard, and quiz screens are easy to understand because labels and actions are visible and direct.</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Concise</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>4/5</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>The interface keeps the flow focused and avoids unnecessary clutter, especially on the quiz screen.</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Familiar</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>5/5</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>The layout follows familiar patterns such as login forms, dashboard cards, and radio-button answer choices.</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Responsive</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>4/5</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>The design is adaptable to different screen sizes and retains control accessibility on smaller screens.</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Consistent</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>4/5</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Buttons, card layouts, and status elements stay visually consistent across screens.</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Attractive</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>4/5</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>The portal uses clean spacing, soft colors, and modern card styling that feels professional.</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Efficient</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>3/5</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>The flow is mostly efficient, but submission confirmation and saved-state feedback are not as strong as they could be.</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Forgiving</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>3/5</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>The interface allows recovery in many cases, but accidental submission would be difficult to reverse without stronger confirmation.</w:t></w:r></w:p></w:tc>
  </w:tr>
</w:tbl>
"@,
    New-Paragraph 'B2. Nielsen heuristics', 'Heading2',
    New-Paragraph 'Heuristic 1: Visibility of system status. The timer and progress bar are strong examples of visible system status. A fix would be to add a brief “Answer saved” message after each response to confirm that the system has accepted the input. Heuristic 2: Match between system and the real world. The labels “Dashboard,” “Available Quizzes,” and “Start Quiz” match students’ expectations. A fix would be to use more exam-specific language such as “Attempt 1” and “Submit assessment.” Heuristic 3: Error prevention. A common failure is the lack of a strong confirmation before final submission. A fix would be a confirmation pop-up that asks: “Are you sure you want to submit?”',
    New-Paragraph 'B3. Golden Rules', 'Heading2',
    New-Paragraph 'Rule 1: Consistency and standards. The portal keeps consistent design patterns across screens. Rule 2: Offer informative feedback. The timer and progress bar are useful, but answer-saving feedback is weak. Rule 3: Prevent errors. Final submission should require confirmation to avoid accidental completion. Rule 4: Reduce short-term memory load. The one-question-at-a-time design reduces mental load and is appropriate for an exam interface.',
    New-Paragraph 'B4. Mistakes and trends', 'Heading2',
    New-Paragraph 'Three common design mistakes in the interface are: (1) weak submission confirmation, (2) limited feedback after answer selection, and (3) missing stronger status cues for user confidence. One current UI trend used here is the minimal card-based dashboard design. This trend helps clarity and modernity, but it could harm accessibility if contrast or button sizing is not strong enough for all users.',
    New-Paragraph '4. Part C: Mini UCD plan for your project title', 'Heading1',
    New-Paragraph 'Understand', 'Heading2',
    New-Paragraph 'Primary persona: Jane is a 19-year-old first-year student at a Ugandan university. She often uses a smartphone and has limited time before assessment deadlines. Her main goal is to log in, find the correct quiz, answer questions, and submit before the timeline ends. The context of use is a university campus environment where students need a fast, simple, and reliable online assessment experience.',
    New-Paragraph 'Specify', 'Heading2',
    New-Paragraph 'The problem is that many students face confusion and stress when using online assessment portals because the system may not clearly show status, timing, or the path to submission. A student should be able to complete an online assessment with less anxiety and fewer errors. Three measurable usability requirements are: (1) a first-time student can log in and start the correct quiz in under 2 minutes without assistance, (2) a student can answer a 10-question assessment with no more than 2 errors, and (3) a student can understand the timer and submission flow and finish the task with a satisfaction score of at least 4 out of 5.',
    New-Paragraph 'Design (low fidelity)', 'Heading2',
    New-Paragraph 'The key design screen would be the quiz-taking page with a timer at the top, question number in the middle, answer options below, and a clear final submission button at the end. The visual metaphor is the examination desk, and the interaction style is direct manipulation with form-based input. Two visible affordances would be the Start Quiz button and the answer option selection buttons.',
    New-Paragraph 'Evaluate', 'Heading2',
    New-Paragraph 'I would test the low-fidelity sketch with five students using a short task: log in, find the quiz, answer a question, and submit. I would measure task success, time taken, number of errors, and user satisfaction. I would check two heuristics first: visibility of system status and error prevention.',
    New-Paragraph 'Iterate', 'Heading2',
    New-Paragraph 'After the first test, I would expect two changes: (1) stronger submission confirmation, because students may accidentally complete early, and (2) clearer timer and status presentation, because time pressure creates anxiety and confusion. These changes are likely to reduce errors and improve confidence.',
    New-Paragraph '5. Conclusion', 'Heading1',
    New-Paragraph 'The KoKCS Online Assessment Portal is a suitable real example of an examination portal UI because it contains the core features of a modern student assessment system. It is clear, familiar, and easy to navigate in most areas, but it still needs stronger feedback, clearer final confirmation, and more visible system status. These findings are useful because they show what should be improved in a student-centered exam interface and how the first UCD cycle should prioritize clarity, speed, and confidence.',
    New-Paragraph 'Appendix A: Evidence screenshots and notes', 'Heading1',
    New-Paragraph 'Figure 1: Login page of the live system, showing the email and password form used to enter the exam portal. Figure 2: Dashboard of the live portal, showing available tasks and the student/admin structure. Figure 3: Quiz-related status area, showing timer/progress indicators. Figure 4: Student exam flow screenshot showing option-based selection and direct interaction. Figure 5: Final action area showing the exit/submit decision point. These screenshots were observed from the live system at https://online-quiz-m8ru.onrender.com/ on 16 September 2026.',
    New-Paragraph 'This document was prepared using the live system for Group 10: Online Examination Portal UI.',
    New-Paragraph 'Note: The assignment was evaluated using the live KoKCS system, and all analysis remains within the lecture scope of UI, usability, human factors, and user-centered design.'
)

$paragraphXml = $parts -join "`n"

$documentXml = @"
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:body>
    $paragraphXml
    <w:sectPr>
      <w:pgSz w:w="12240" w:h="15840"/>
      <w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="720" w:footer="720" w:gutter="0"/>
    </w:sectPr>
  </w:body>
</w:document>
"@

Set-Content -Path (Join-Path $tempRoot 'word\document.xml') -Value $documentXml -Encoding UTF8

$zipPath = Join-Path $projectRoot 'assignment\Group10_UI_Assignment.zip'
if (Test-Path $docxPath) { Remove-Item $docxPath -Force }
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }

Compress-Archive -Path (Join-Path $tempRoot '*') -DestinationPath $zipPath -Force
Move-Item -Path $zipPath -Destination $docxPath -Force
Remove-Item $tempRoot -Recurse -Force

Get-Item $docxPath | Select-Object FullName, Length
