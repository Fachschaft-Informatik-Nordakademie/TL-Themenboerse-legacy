import { Button, Container, TextField } from "@material-ui/core";
import { makeStyles } from '@material-ui/core/styles';
import { Autocomplete, Value } from "@material-ui/lab";
import { KeyboardDatePicker } from "@material-ui/pickers";
import { useFormik } from "formik";
import * as yup from 'yup';
import React from "react";

interface IFormValues {
    readonly title: string;
    readonly description: string;
    readonly requirements: string;
    readonly deadline: Date | null;
    readonly tags: string[];
}

const useStyles = makeStyles((theme) => ({
    formField: {
        marginTop: theme.spacing(2),
        marginBottom: theme.spacing(2)
    },
}));

const validationSchema = yup.object({
    title: yup
        .string()
        .required('Titel ist ein Pflichtfeld'),
    description: yup
        .string()
        .required('Beschreibung ist ein Pflichtfeld'),
    requirements: yup
        .string()
        .required('Beschreibung ist ein Pflichtfeld'),
    deadline: yup.date().nullable(),
    tags: yup
        .array()
        .ensure()
});


function create() {

    const submitForm = async (values: IFormValues) => {
        const topicData = {
            title: values.title,
            description: values.description,
            requirements: values.requirements,
            deadline: values.deadline,
            tags: values.tags
        }
        const response = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL}/topic`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(topicData),
        });
        // TODO: success message/back navigation? error handling?
    };

    const formik = useFormik<IFormValues>({
        initialValues: {
            title: '',
            description: '',
            tags: [],
            deadline: null,
            requirements: ''
        },
        validationSchema: validationSchema,
        onSubmit: (values) => {
            console.log("test");
            submitForm(values);
        },
    });
    
    console.log("errors", formik.errors, formik.touched);
    const classes = useStyles();

    return <Container>
        <h1>Neues Thema erstellen</h1>
        <form onSubmit={(e) => { e.preventDefault(); formik.submitForm() }}>
            <TextField label="Titel" name="title" fullWidth className={classes.formField} value={formik.values.title} onChange={formik.handleChange}
                helperText={formik.touched.title && formik.errors.title} error={formik.touched.title && Boolean(formik.errors.title)}
                onBlur={formik.handleBlur} required />
            <TextField label="Beschreibung" name="description" fullWidth multiline className={classes.formField} value={formik.values.description} onChange={formik.handleChange}
                helperText={formik.touched.description && formik.errors.description} error={formik.touched.description && Boolean(formik.errors.description)}
                onBlur={formik.handleBlur} required />
            <TextField label="Anforderungen" name="requirements" fullWidth multiline className={classes.formField} value={formik.values.requirements} onChange={formik.handleChange}
                helperText={formik.touched.requirements && formik.errors.requirements} error={formik.touched.requirements && Boolean(formik.errors.requirements)}
                onBlur={formik.handleBlur} required />
            <KeyboardDatePicker
                name="deadline"
                disableToolbar
                variant="inline"
                format="dd/MM/yyyy"
                margin="normal"
                label="Endtermin"
                value={formik.values.deadline}
                onChange={(value) => formik.setFieldValue('deadline', value)}
                helperText={formik.touched.deadline && formik.errors.deadline}
                error={formik.touched.deadline && Boolean(formik.errors.deadline)}
                onBlur={formik.handleBlur}
            />
            <Autocomplete
                id="tags"
                className={classes.formField}
                multiple
                freeSolo
                value={formik.values.tags}
                onChange={(e, values) => formik.setFieldValue('tags', values)}
                options={tagOptions}
                getOptionLabel={(option) => option}
                defaultValue={[]}
                onBlur={formik.handleBlur}
                renderInput={(params) => (
                    <TextField
                        {...params}
                        variant="standard"
                        label="Tags"
                        helperText={formik.touched.tags && formik.errors.tags}
                        error={formik.touched.tags && Boolean(formik.errors.tags)}
                    />
                )}
            />
            <Button variant="contained" color="primary" type="submit">
                Erstellen
            </Button>
        </form>
    </Container>;
}

const tagOptions: string[] = [];// TODO: hard coded defaults or values loaded from the backend?

export default create;