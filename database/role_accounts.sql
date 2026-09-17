-- Idempotent role-account setup for an existing PostgreSQL database.
-- Apply after the users table has been created.
INSERT INTO users (name, email, password_hash, role, class_level, subject)
VALUES
    ('Admin User', 'admin@kokcs.edu.ug', '$2y$10$oCrM.CMFArVx2KfoFUXv1Om7hKah8QbYA4JKnOv4sJBoeDc.BXaiO', 'admin', NULL, NULL),
    ('Pretty Supervisor', 'pretty@kokcs.edu.ug', '$2y$10$txIFfHo3cMduy/3i2Ikbie291kvCRQ/Pfsk4DpisVbZp08hsRV.CK', 'supervisor', 'S4', 'Mathematics'),
    ('Lameck Teacher', 'lameck@kokcs.edu.ug', '$2y$10$RKD8fJSmeoajpiBwH/oy9.jXs6wbr9rdXTyBEpYXkR/BD2nt.8joO', 'teacher', NULL, 'Mathematics'),
    ('Eriya Student', 'eriya@kokcs.edu.ug', '$2y$10$Vl.jtwN3xXm/uverjoYyw.eIc/pxT6g/0Za.KpX2hHTkeORcJQxk6', 'student', 'S4', 'Mathematics')
ON CONFLICT (email) DO UPDATE
SET name = EXCLUDED.name,
    password_hash = EXCLUDED.password_hash,
    role = EXCLUDED.role,
    class_level = EXCLUDED.class_level,
    subject = EXCLUDED.subject;
