import { Button, Checkbox, FormControlLabel, TextField, Tooltip, Typography } from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
import { Autocomplete } from '@material-ui/lab';
import { KeyboardDatePicker } from '@material-ui/pickers';
import { useFormik } from 'formik';
import * as yup from 'yup';
import React, { useState } from 'react';
import Head from 'next/head';
import Link from 'next/link';
import { useTranslation } from 'next-i18next';
import { useEffect } from 'react';
import { NextRouter, useRouter } from 'next/router';

interface IFormValues {
  readonly title: string;
  readonly description: string;
  readonly requirements: string;
  readonly scope: string;
  readonly pages: number;
  readonly start: Date | null;
  readonly deadline: Date | null;
  readonly tags: string[];
  readonly website: string;
}

const useStyles = makeStyles((theme) => ({
  formField: {
    marginTop: theme.spacing(2),
    marginBottom: theme.spacing(2),
  },
  deadlineField: {
    marginLeft: theme.spacing(2),
  },
  cancelButton: {
    marginLeft: theme.spacing(2),
  },
}));

export function topicForm(
  headerId: string,
  submitId: string,
  effect: (router: NextRouter, formik: { setFieldValue: (field: string, value: unknown) => void }) => void,
  submit: (router: NextRouter, values: IFormValues) => void,
): () => JSX.Element {
  return () => {
    const { t: tCommon } = useTranslation('common');
    const { t: tTopic } = useTranslation('topic');

    const router = useRouter();
    const [check, checkChange] = useState(false);

    const validationSchema = yup.object({
      title: yup.string().required(tTopic('messageTitleRequired')),
      description: yup.string().required(tTopic('messageDescriptionRequired')),
      requirements: yup.string().required(tTopic('messageRequirementsRequired')),
      start: yup.date().nullable(),
      deadline: yup.date().nullable(),
      tags: yup.array().ensure(),
      pages: yup.number().nullable(),
      website: yup.string().nullable().url(tTopic('messageUrlInvalid')),
    });

    const formik = useFormik<IFormValues>({
      initialValues: {
        title: '',
        description: '',
        scope: '',
        tags: [],
        start: null,
        deadline: null,
        requirements: '',
        pages: 0,
        website: '',
      },

      validationSchema: validationSchema,
      onSubmit: (values) => {
        submit(router, values);
      },
    });

    useEffect(() => {
      effect(router, formik);
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const classes = useStyles();

    return (
      <>
        <Head>
          <title>
            {tTopic(headerId)} - {tCommon('appName')}
          </title>
        </Head>
        <>
          <Typography gutterBottom variant="h4" component="h2">
            {tTopic(headerId)}
          </Typography>
          <form
            noValidate
            onSubmit={(e) => {
              e.preventDefault();
              formik.submitForm();
            }}
          >
            <TextField
              label={tTopic('labelTitel')}
              name="title"
              fullWidth
              className={classes.formField}
              value={formik.values.title}
              onChange={formik.handleChange}
              helperText={formik.touched.title && formik.errors.title}
              error={formik.touched.title && Boolean(formik.errors.title)}
              onBlur={formik.handleBlur}
              required
            />
            <TextField
              label={tTopic('labelDescription')}
              name="description"
              fullWidth
              multiline
              className={classes.formField}
              value={formik.values.description}
              onChange={formik.handleChange}
              helperText={formik.touched.description && formik.errors.description}
              error={formik.touched.description && Boolean(formik.errors.description)}
              onBlur={formik.handleBlur}
              required
            />
            <TextField
              label={tTopic('labelRequirements')}
              name="requirements"
              fullWidth
              multiline
              className={classes.formField}
              value={formik.values.requirements}
              onChange={formik.handleChange}
              helperText={formik.touched.requirements && formik.errors.requirements}
              error={formik.touched.requirements && Boolean(formik.errors.requirements)}
              onBlur={formik.handleBlur}
              required
            />
            <TextField
              label={tTopic('labelPages')}
              name="pages"
              fullWidth
              type="number"
              className={classes.formField}
              value={formik.values.pages}
              onChange={formik.handleChange}
              helperText={formik.touched.pages && formik.errors.pages}
              error={formik.touched.pages && Boolean(formik.errors.pages)}
              onBlur={formik.handleBlur}
            />
            <TextField
              label={tTopic('labelWebsite')}
              name="website"
              fullWidth
              className={classes.formField}
              value={formik.values.website}
              onChange={formik.handleChange}
              helperText={formik.touched.website && formik.errors.website}
              error={formik.touched.website && Boolean(formik.errors.website)}
              onBlur={formik.handleBlur}
            />
            <KeyboardDatePicker
              name="start"
              disableToolbar
              variant="inline"
              format="dd.MM.yyyy"
              margin="normal"
              label={tTopic('labelStartdate')}
              value={formik.values.start}
              onChange={(value) => formik.setFieldValue('start', value)}
              helperText={formik.touched.start && formik.errors.start}
              error={formik.touched.start && Boolean(formik.errors.start)}
              onBlur={formik.handleBlur}
            />
            <KeyboardDatePicker
              className={classes.deadlineField}
              name="deadline"
              disableToolbar
              variant="inline"
              format="dd.MM.yyyy"
              margin="normal"
              label={tTopic('labelEnddate')}
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
                  label={tTopic('labelTags')}
                  helperText={formik.touched.tags && formik.errors.tags}
                  error={formik.touched.tags && Boolean(formik.errors.tags)}
                />
              )}
            />
            <FormControlLabel
              control={<Checkbox checked={check} onChange={(_, checked) => checkChange(checked)} color="primary" />}
              label={tTopic('scientificLabel')}
            />
            <Tooltip title={check ? '' : tTopic('scientificTooltip')}>
              <span>
                <Button variant="contained" color="primary" type="submit" disabled={!check}>
                  {tTopic(submitId)}
                </Button>
              </span>
            </Tooltip>
            <Link href="/">
              <Button className={classes.cancelButton} variant="contained" color="default" type="button">
                {tTopic('buttonCancel')}
              </Button>
            </Link>
          </form>
        </>
      </>
    );
  };
}

const tagOptions: string[] = []; // TODO: hard coded defaults or values loaded from the backend?
