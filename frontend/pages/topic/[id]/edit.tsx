import { Button, Container, TextField, Typography } from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
import { Autocomplete } from '@material-ui/lab';
import { KeyboardDatePicker } from '@material-ui/pickers';
import { useFormik } from 'formik';
import * as yup from 'yup';
import React from 'react';
import Head from 'next/head';
import Link from 'next/link';
import axiosClient from '../../../src/api';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { fetchUser } from '../../../src/server/fetchUser';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import { useTranslation } from 'next-i18next';
import { useEffect, useState } from 'react';
import { Topic } from '../../../src/types/topic';
import { User } from '../../../src/types/user';
import { useRouter } from 'next/router';


type Props = {
    user: User;
  };

  export async function getServerSideProps(context: GetServerSidePropsContext): Promise<GetServerSidePropsResult<Props>> {
    const user = await fetchUser(context.req.cookies);
  
    if (user === null) {
      return { redirect: { destination: '/login', permanent: false } };
    }
  
    return {
      props: { user, ...(await serverSideTranslations('de', ['common', 'topic-edit'])) },
    };
  }

interface IFormValues {
  readonly title: string;
  readonly description: string;
  readonly requirements: string;
  readonly pages: number;
  readonly start: String | '';
  readonly deadline: String | '';
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


export default function EditTopic({ user }: Props): JSX.Element {
  const { t: tCommon } = useTranslation('common');
  const { t: tEditTopic } = useTranslation('topic-edit');
  

  const [data, setData] = useState<Topic>();
  const router = useRouter();
  const id = router.query.id;

  console.log(id);

  const fetchData = async (): Promise<void> => {
   const response = await axiosClient.get<Topic>(`/topic/${id}`);
   //const response = await axiosClient.get<Topic>(`/topic/4`);

    setData(response.data);
  };


  useEffect(() => {
    fetchData();
  }, [id]);
  


  const validationSchema = yup.object({
    title: yup.string().required(tEditTopic('messageTitleRequired')),
    description: yup.string().required(tEditTopic('messageDescriptionRequired')),
    requirements: yup.string().required(tEditTopic('messageRequirementsRequired')),
    start: yup.date().nullable(),
    deadline: yup.date().nullable(),
    tags: yup.array().ensure(),
    pages: yup.number().nullable(),
    website: yup.string().nullable().url(tEditTopic('messageUrlInvalid')),
  });

  const submitForm = async (values: IFormValues): Promise<void> => {
    await axiosClient.put(`/topic/${id}`, values);
    // TODO: success message/back navigation? error handling?
  };

  const formik = useFormik<IFormValues>({

    initialValues: {
      title: data.title ?? "",
      description: data.description ?? "",
      tags: data.tags ? data.tags : null,
      start:  data.start ? data.start : "",
      deadline: data.deadline ? data.deadline : "",
      /*start: null,
      deadline: null,*/
      requirements: data.requirements ? data.requirements : "",
      pages: data.pages ? data.pages : 0,
      website: data.website ? data.website : "",
    },
    

    /*const formik = useFormik<IFormValues>({
      initialValues: {
          title: '',
        description: '',
        tags: null,
        start:  '',
        deadline: '',
       /* start: null,
        deadline: null,
        requirements: '',
        pages: 0,
        website: '',
      },*/


    validationSchema: validationSchema,
    onSubmit: (values) => {
      console.log('submitting topic!');
      submitForm(values);
    },
  });

  const classes = useStyles();

  return (
    <>
      <Head>
        <title>
          {tEditTopic('title')} - {tCommon('appName')}
        </title>
      </Head>
      <>
        <Typography gutterBottom variant="h4" component="h2">
          {tEditTopic('headline')}
        </Typography>
        <form
          noValidate
          onSubmit={(e) => {
            e.preventDefault();
            formik.submitForm();
          }}
        >
          <TextField
            label={tEditTopic('labelTitel')}
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
            label={tEditTopic('labelDescription')}
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
            label={tEditTopic('labelRequirements')}
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
            label={tEditTopic('labelPages')}
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
            label={tEditTopic('labelWebsite')}
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
            label={tEditTopic('labelStartdate')}
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
            label={tEditTopic('labelEnddate')}
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
                label={tEditTopic('labelTags')}
                helperText={formik.touched.tags && formik.errors.tags}
                error={formik.touched.tags && Boolean(formik.errors.tags)}
              />
            )}
          />
          <Button variant="contained" color="primary" type="submit">
            {tEditTopic('buttonSubmit')}
          </Button>
          <Link href="/">
            <Button className={classes.cancelButton} variant="contained" color="default" type="button">
              {tEditTopic('buttonCancel')}
            </Button>
          </Link>
        </form>
      </>
    </>
  );
}

const tagOptions: string[] = []; // TODO: hard coded defaults or values loaded from the backend?

EditTopic.layout = 'main';
